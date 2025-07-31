<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\Team;
use App\Models\User;
use App\Models\Site;
use App\Models\TicketDraft;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

class TicketView extends Component
{
    public Company $company;
    public Ticket $ticket;
    public $customerMessage = '';
    public $internalMessage = '';
    public $isLoadingCustomer = false;
    public $isLoadingInternal = false;
    public $messageType = 'internal'; // Default to internal messages
    public $filters = []; // Filters for timeline: customer_chat, internal_chat, activity_updates

    // Modal states
    public $showChangeSiteModal = false;
    public $showStatusConfirmModal = false;
    public $showOnHoldModal = false;
    public $showAssignTeamModal = false;
    public $showAssignUserModal = false;
    public $showUnassignTeamModal = false;
    public $showUnassignUserModal = false;

    // Form data
    public $newSiteId = null;
    public $pendingStatus = null;
    public $holdDuration = null;
    public $customHoldUntil = null;
    public $holdReason = '';
    public $assignToTeam = null;
    public $assignToUser = null;

    protected $listeners = [
        'messageAdded' => '$refresh',
        'ticketUpdated' => '$refresh',
    ];

    public function getListeners()
    {
        $listeners = $this->listeners;

        if ($this->ticket) {
            $listeners["echo-private:ticket.{$this->ticket->id},ticket.updated"] = 'handleTicketUpdate';
            \Log::info('Company view listening to channel', ['channel' => "ticket.{$this->ticket->id}"]);
        }

        return $listeners;
    }

    public function handleTicketUpdate()
    {
        \Log::info('Company view received ticket update', ['ticket_id' => $this->ticket->id]);
        $this->ticket->refresh();
        $this->ticket->load(['messages.user', 'activities.user']);
    }

    public function mount($company = null, $ticket = null)
    {
        // Handle route model binding
        if ($company instanceof Company) {
            $this->company = $company;
        } else {
            $routeCompany = request()->route('company');
            if ($routeCompany instanceof Company) {
                $this->company = $routeCompany;
            } else {
                $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
            }
        }

        if ($ticket instanceof Ticket) {
            $this->ticket = $ticket;
        } else {
            $this->ticket = Ticket::findOrFail(request()->route('ticket'));
        }

        // Ensure ticket belongs to this company
        if (!$this->ticket->site || $this->ticket->site->company_id !== $this->company->id) {
            abort(403, 'You do not have access to this ticket.');
        }

        // Set default assignment values
        $this->assignToTeam = $this->ticket->assigned_team_id;
        $this->assignToUser = $this->ticket->assigned_user_id;
        $this->newSiteId = $this->ticket->site_id;

        // Load existing draft if it exists
        $existingDraft = TicketDraft::where('ticket_id', $this->ticket->id)
            ->where('user_id', auth()->id())
            ->where('draft_type', 'customer')
            ->first();

        if ($existingDraft) {
            $this->customerMessage = $existingDraft->content;
        }
    }

    public function getCombinedTimelineProperty()
    {
        $messages = $this->ticket->messages()
            ->with('user')
            ->get()
            ->map(function ($message) {
                return (object) [
                    'type' => 'message',
                    'data' => $message,
                    'created_at' => $message->created_at,
                ];
            });

        $activities = $this->ticket->activities()
            ->with('user')
            ->get()
            ->map(function ($activity) {
                return (object) [
                    'type' => 'activity',
                    'data' => $activity,
                    'created_at' => $activity->created_at,
                ];
            });

        // Don't include drafts in the main timeline - they'll be shown separately
        $allItems = $messages->concat($activities)
            ->sortByDesc('created_at');

        // Apply filters if any are selected
        if (!empty($this->filters)) {
            $allItems = $allItems->filter(function ($item) {
                if ($item->type === 'message') {
                    $messageType = $item->data->message_type;
                    if ($messageType === 'customer' && in_array('customer_chat', $this->filters)) {
                        return true;
                    }
                    if ($messageType === 'company' && in_array('customer_chat', $this->filters)) {
                        return true;
                    }
                    if ($messageType === 'internal' && in_array('internal_chat', $this->filters)) {
                        return true;
                    }
                } elseif ($item->type === 'activity' && in_array('activity_updates', $this->filters)) {
                    return true;
                }
                return false;
            });
        }

        // Group items by minute
        $groupedItems = collect();
        $currentGroup = null;
        $currentMinute = null;

        foreach ($allItems as $item) {
            $itemMinute = $item->created_at->format('Y-m-d H:i');

            if ($currentMinute !== $itemMinute) {
                // Start a new group
                if ($currentGroup) {
                    $groupedItems->push($currentGroup);
                }

                $currentGroup = (object) [
                    'type' => 'group',
                    'minute' => $itemMinute,
                    'timestamp' => $item->created_at,
                    'items' => collect([$item])
                ];
                $currentMinute = $itemMinute;
            } else {
                // Add to current group
                $currentGroup->items->push($item);
            }
        }

        // Don't forget the last group
        if ($currentGroup) {
            $groupedItems->push($currentGroup);
        }

        // Post-process to group consecutive internal messages from the same user within each minute group
        $groupedItems = $groupedItems->map(function ($group) {
            $newItems = collect();
            $currentInternalGroup = null;
            $currentUser = null;

            // Reverse items within this group to process them in chronological order (oldest first)
            $itemsInChronologicalOrder = $group->items->reverse();

            foreach ($itemsInChronologicalOrder as $item) {
                if ($item->type === 'message' && $item->data->message_type === 'internal') {
                    $itemUserId = $item->data->user_id;

                    if ($currentUser === $itemUserId && $currentInternalGroup) {
                        // Add to existing internal group
                        $currentInternalGroup->messages->push($item->data);
                    } else {
                        // Save previous group if exists
                        if ($currentInternalGroup) {
                            $newItems->push($currentInternalGroup);
                        }

                        // Start new internal group
                        $currentInternalGroup = (object) [
                            'type' => 'internal_group',
                            'user' => $item->data->user,
                            'messages' => collect([$item->data]),
                            'created_at' => $item->created_at,
                        ];
                        $currentUser = $itemUserId;
                    }
                } else {
                    // Save any pending internal group
                    if ($currentInternalGroup) {
                        $newItems->push($currentInternalGroup);
                        $currentInternalGroup = null;
                        $currentUser = null;
                    }

                    // Add non-internal item
                    $newItems->push($item);
                }
            }

            // Don't forget the last internal group
            if ($currentInternalGroup) {
                $newItems->push($currentInternalGroup);
            }

            // Reverse the final items back to descending order for display
            $group->items = $newItems->reverse();
            return $group;
        });

        return $groupedItems;
    }

    public function getCurrentDraftsProperty()
    {
        // Get current customer drafts (only show if customer_chat filter is active or no filters)
        if (!empty($this->filters) && !in_array('customer_chat', $this->filters)) {
            return collect();
        }

        return $this->ticket->drafts()
            ->with('user')
            ->customerDrafts()
            ->where('content', '!=', '')
            ->get();
    }

    public function setMessageType($type)
    {
        if (in_array($type, ['internal', 'customer'])) {
            $this->messageType = $type;
        }
    }

    public function updatedCustomerMessage()
    {
        // Save draft as user types customer message
        if ($this->messageType === 'customer') {
            \Log::info('Saving draft', [
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'content' => $this->customerMessage
            ]);
            $this->saveDraft('customer', $this->customerMessage);
        }
    }

    public function saveDraft($type, $content)
    {
        if (empty(trim($content))) {
            // Delete draft if content is empty
            \Log::info('Deleting empty draft', ['ticket_id' => $this->ticket->id, 'user_id' => auth()->id(), 'type' => $type]);
            TicketDraft::where('ticket_id', $this->ticket->id)
                ->where('user_id', auth()->id())
                ->where('draft_type', $type)
                ->delete();
            return;
        }

        \Log::info('Creating/updating draft', [
            'ticket_id' => $this->ticket->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'content' => $content
        ]);

        $draft = TicketDraft::updateOrCreate(
            [
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'draft_type' => $type,
            ],
            [
                'content' => trim($content),
            ]
        );

        \Log::info('Draft saved', ['draft_id' => $draft->id]);

        // Broadcast draft update for real-time collaboration
        $this->dispatch('draftUpdated');
    }

    public function clearDraft($type)
    {
        TicketDraft::where('ticket_id', $this->ticket->id)
            ->where('user_id', auth()->id())
            ->where('draft_type', $type)
            ->delete();
    }

    public function discardDraft()
    {
        // Clear the customer draft from database
        $this->clearDraft('customer');

        // Clear the customer message field
        $this->customerMessage = '';

        // Optionally switch back to internal chat
        $this->messageType = 'internal';

        // Refresh the drafts display
        $this->dispatch('draftUpdated');

        session()->flash('message', 'Draft discarded successfully.');
    }

    public function getInternalMessagesProperty()
    {
        return $this->ticket->messages()
            ->with('user')
            ->where('message_type', 'internal')
            ->orderBy('created_at', 'desc') // Changed from orderBy to desc
            ->get();
    }

    public function getActivitiesProperty()
    {
        return $this->ticket->activities()
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function sendCustomerMessage()
    {
        $this->validate([
            'customerMessage' => 'required|string|min:1|max:2000'
        ]);

        $this->isLoadingCustomer = true;

        try {
            $this->ticket->messages()->create([
                'user_id' => auth()->id(),
                'message_type' => 'company',
                'content' => trim($this->customerMessage),
            ]);

            // Clear the draft after sending
            $this->clearDraft('customer');

            $this->customerMessage = '';
            $this->dispatch('messageAdded');
            $this->dispatch('scrollToBottom');

            // Also broadcast for real-time updates
            broadcast(new \App\Events\TicketUpdated($this->ticket));

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message. Please try again.');
        } finally {
            $this->isLoadingCustomer = false;
        }
    }

    public function sendInternalMessage()
    {
        $this->validate([
            'internalMessage' => 'required|string|min:1|max:2000'
        ]);

        $this->isLoadingInternal = true;

        try {
            $this->ticket->messages()->create([
                'user_id' => auth()->id(),
                'message_type' => 'internal',
                'content' => trim($this->internalMessage),
            ]);

            $this->internalMessage = '';
            $this->dispatch('messageAdded');
            $this->dispatch('scrollToBottom');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send internal note. Please try again.');
        } finally {
            $this->isLoadingInternal = false;
        }
    }

    // Site Change Methods
    public function openChangeSiteModal()
    {
        $this->showChangeSiteModal = true;
    }

    public function closeChangeSiteModal()
    {
        $this->showChangeSiteModal = false;
        $this->newSiteId = $this->ticket->site_id;
    }

    public function changeSite()
    {
        $this->validate([
            'newSiteId' => 'required|exists:sites,id'
        ]);

        // Check if the site has actually changed
        if ($this->newSiteId == $this->ticket->site_id) {
            $this->closeChangeSiteModal();
            return; // No change needed
        }

        $oldSite = $this->ticket->site->name;
        $newSite = Site::find($this->newSiteId);

        // Ensure the new site belongs to this company
        if ($newSite->company_id !== $this->company->id) {
            session()->flash('error', 'Invalid site selected.');
            return;
        }

        $this->ticket->update(['site_id' => $this->newSiteId]);
        $this->ticket->logActivity('site_changed', "Site changed from {$oldSite} to {$newSite->name}", $oldSite, $newSite->name, auth()->id());

        $this->closeChangeSiteModal();
        $this->dispatch('messageAdded');
    }

    // Status Change Methods
    public function confirmStatusChange($status)
    {
        if ($status === $this->ticket->status) {
            return; // No change needed
        }

        $this->pendingStatus = $status;
        $this->showStatusConfirmModal = true;
    }

    public function closeStatusConfirmModal()
    {
        $this->showStatusConfirmModal = false;
        $this->pendingStatus = null;
    }

    public function confirmStatusUpdate()
    {
        if ($this->pendingStatus && in_array($this->pendingStatus, ['open', 'awaiting_customer', 'closed'])) {
            $this->ticket->updateStatus($this->pendingStatus, auth()->id());
            $this->dispatch('messageAdded');
        }
        $this->closeStatusConfirmModal();
    }

    // On Hold Methods
    public function openOnHoldModal()
    {
        $this->showOnHoldModal = true;
        $this->holdDuration = null;
        $this->customHoldUntil = null;
        $this->holdReason = '';
    }

    public function closeOnHoldModal()
    {
        $this->showOnHoldModal = false;
        $this->holdDuration = null;
        $this->customHoldUntil = null;
        $this->holdReason = '';
    }

    public function putOnHold()
    {
        $this->validate([
            'holdDuration' => 'required',
            'customHoldUntil' => 'required_if:holdDuration,custom',
        ]);

        // Calculate hold until datetime
        $holdUntil = null;
        if ($this->holdDuration === 'custom') {
            $holdUntil = Carbon::parse($this->customHoldUntil);
        } else {
            $holdUntil = match($this->holdDuration) {
                '1_hour' => now()->addHour(),
                '2_hours' => now()->addHours(2),
                '4_hours' => now()->addHours(4),
                '8_hours' => now()->addHours(8),
                '1_day' => now()->addDay(),
                '2_days' => now()->addDays(2),
                '1_week' => now()->addWeek(),
                default => now()->addDay(),
            };
        }

        // Update ticket with hold information
        $this->ticket->update([
            'status' => 'on_hold',
            'hold_until' => $holdUntil,
            'hold_reason' => $this->holdReason ?: null,
        ]);

        // Log activity with hold information
        $description = "Ticket put on hold until " . $holdUntil->format('M j, Y g:i A');
        if ($this->holdReason) {
            $description .= " - Reason: " . $this->holdReason;
        }

        $this->ticket->logActivity('status_changed', $description, 'open', 'on_hold', auth()->id());

        $this->closeOnHoldModal();
        $this->dispatch('messageAdded');
    }

    // Team Assignment Methods
    public function openAssignTeamModal()
    {
        $this->showAssignTeamModal = true;
        $this->assignToTeam = $this->ticket->assigned_team_id;
    }

    public function closeAssignTeamModal()
    {
        $this->showAssignTeamModal = false;
        $this->assignToTeam = $this->ticket->assigned_team_id;
    }

    public function confirmTeamAssignment()
    {
        $this->validate([
            'assignToTeam' => 'nullable|exists:teams,id',
        ]);

        if ($this->assignToTeam !== $this->ticket->assigned_team_id) {
            $this->ticket->assignToTeam($this->assignToTeam, auth()->id());
            $this->dispatch('messageAdded');
        }

        $this->closeAssignTeamModal();
    }

    public function confirmUnassignTeam()
    {
        $this->showUnassignTeamModal = true;
    }

    public function closeUnassignTeamModal()
    {
        $this->showUnassignTeamModal = false;
    }

    public function unassignTeam()
    {
        $this->ticket->assignToTeam(null, auth()->id());
        $this->closeUnassignTeamModal();
        $this->dispatch('messageAdded');
    }

    // User Assignment Methods
    public function openAssignUserModal()
    {
        $this->showAssignUserModal = true;
        $this->assignToUser = $this->ticket->assigned_user_id;
    }

    public function closeAssignUserModal()
    {
        $this->showAssignUserModal = false;
        $this->assignToUser = $this->ticket->assigned_user_id;
    }

    public function confirmUserAssignment()
    {
        $this->validate([
            'assignToUser' => 'nullable|exists:users,id',
        ]);

        if ($this->assignToUser !== $this->ticket->assigned_user_id) {
            $this->ticket->assignToUser($this->assignToUser, auth()->id());
            $this->dispatch('messageAdded');
        }

        $this->closeAssignUserModal();
    }

    public function confirmUnassignUser()
    {
        $this->showUnassignUserModal = true;
    }

    public function closeUnassignUserModal()
    {
        $this->showUnassignUserModal = false;
    }

    public function unassignUser()
    {
        $this->ticket->assignToUser(null, auth()->id());
        $this->closeUnassignUserModal();
        $this->dispatch('messageAdded');
    }

    public function getTeamsProperty()
    {
        return $this->company->teams()->get();
    }

    public function getCompanyUsersProperty()
    {
        return $this->company->users()->get();
    }

    public function getAvailableSitesProperty()
    {
        return $this->company->sites()->get();
    }

    public function getStatusColorClass($status)
    {
        return match($status) {
            'open' => 'text-green-600 bg-green-100',
            'awaiting_customer' => 'text-amber-600 bg-amber-100',
            'on_hold' => 'text-gray-600 bg-gray-100',
            'closed' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function getStatusLabel($status)
    {
        return match($status) {
            'open' => 'Open',
            'awaiting_customer' => 'Awaiting Customer',
            'on_hold' => 'On Hold',
            'closed' => 'Closed',
            default => ucfirst($status)
        };
    }

    #[Layout('components.layouts.company')]
    public function render()
    {
        return view('livewire.company.ticket-view');
    }
}
