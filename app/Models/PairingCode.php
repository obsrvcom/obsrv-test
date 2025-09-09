<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PairingCode extends Model
{
    protected $fillable = [
        'code',
        'device_id',
        'thing_name',
        'used',
        'site_id',
        'paired_by',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'used' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pairedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paired_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }

    public static function generate(string $deviceId, ?string $thingName = null): self
    {
        // Generate code in format XXXX-XXXX-XXXX
        $segments = [];
        for ($i = 0; $i < 3; $i++) {
            $segments[] = strtoupper(Str::random(4));
        }
        $code = implode('-', $segments);

        return self::create([
            'code' => $code,
            'device_id' => $deviceId,
            'thing_name' => $thingName,
            'expires_at' => now()->addMinutes(15),
        ]);
    }
}
