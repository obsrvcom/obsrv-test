<?php

namespace App\Models;

use App\Events\TicketUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'site_id',
        'created_by_user_id',
        'assigned_team_id',
        'assigned_user_id',
        'status',
        'subject',
        'description',
        'last_customer_message_at',
        'last_company_message_at',
    ];

    protected $casts = [
        'last_customer_message_at' => 'datetime',
        'last_company_message_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assigned_team_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function customerMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->where('message_type', 'customer');
    }

    public function companyMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->where('message_type', 'company');
    }

    public function internalMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->where('message_type', 'internal');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });

        static::created(function ($ticket) {
            $ticket->logActivity('created', 'Ticket created', null, $ticket->status, $ticket->created_by_user_id);
        });
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $lastTicket = self::latest('id')->first();
        $number = $lastTicket ? $lastTicket->id + 1 : 1;

        return $prefix . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function logActivity(string $activityType, string $description, ?string $oldValue = null, ?string $newValue = null, ?int $userId = null): void
    {
        $this->activities()->create([
            'user_id' => $userId ?? auth()->id(),
            'activity_type' => $activityType,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    public function updateStatus(string $status, ?int $userId = null): void
    {
        $oldStatus = $this->status;
        $this->update(['status' => $status]);

        $this->logActivity('status_changed', "Status changed from {$oldStatus} to {$status}", $oldStatus, $status, $userId);

        // Broadcast the ticket update
        broadcast(new TicketUpdated($this));
    }

    public function assignToTeam(?int $teamId, ?int $userId = null): void
    {
        $oldTeam = $this->assignedTeam?->name ?? 'Unassigned';
        $oldTeamId = $this->assigned_team_id;
        $this->update(['assigned_team_id' => $teamId]);

        $newTeam = $teamId ? Team::find($teamId)->name : 'Unassigned';
        $this->logActivity('team_assigned', "Assigned to team: {$newTeam}", $oldTeam, $newTeam, $userId);

        // Broadcast the ticket update
        broadcast(new TicketUpdated($this));
    }

    public function assignToUser(?int $assignedUserId, ?int $userId = null): void
    {
        $oldUser = $this->assignedUser?->name ?? 'Unassigned';
        $this->update(['assigned_user_id' => $assignedUserId]);

        $newUser = $assignedUserId ? User::find($assignedUserId)->name : 'Unassigned';
        $this->logActivity('user_assigned', "Assigned to user: {$newUser}", $oldUser, $newUser, $userId);

        // Broadcast the ticket update
        broadcast(new TicketUpdated($this));
    }

    public function getTimeSinceLastResponseAttribute(): ?string
    {
        $lastResponse = $this->last_company_message_at ?? $this->created_at;
        return $lastResponse->diffForHumans();
    }

    public function getPriorityScoreAttribute(): int
    {
        $lastResponse = $this->last_company_message_at ?? $this->created_at;
        return now()->diffInMinutes($lastResponse);
    }

    public function scopeForSite($query, int $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->whereHas('site', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });
    }

    public function scopeAssignedToTeam($query, int $teamId)
    {
        return $query->where('assigned_team_id', $teamId);
    }

    public function scopeByPriority($query)
    {
        return $query->orderByRaw('COALESCE(last_company_message_at, created_at) ASC');
    }

    public function scopeByLastActivity($query)
    {
        return $query->orderByRaw('
            CASE
                WHEN COALESCE(last_customer_message_at, created_at) > COALESCE(last_company_message_at, created_at)
                THEN COALESCE(last_customer_message_at, created_at)
                ELSE COALESCE(last_company_message_at, created_at)
            END DESC
        ');
    }
}
