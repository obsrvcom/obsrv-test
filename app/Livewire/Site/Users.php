<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\MagicLinkMail;
use Illuminate\Support\Str;
use Flux\Flux;

class Users extends Component
{
    public Site $site;
    public $email = '';
    public $successMessage = null;
    public $errorMessage = null;
    public $showInviteModal = false;
    public $showRemoveModal = false;
    public $userIdToRemove = null;
    public $showResendModal = false;
    public $userIdToResend = null;

    protected $rules = [
        'email' => 'required|email',
    ];

    public function mount(Site $site)
    {
        $this->site = $site;
    }

    public function openInviteModal()
    {
        $this->reset(['email', 'successMessage', 'errorMessage']);
        $this->showInviteModal = true;
    }

    public function closeInviteModal()
    {
        $this->showInviteModal = false;
    }

    public function inviteUser()
    {
        $this->validate();
        $email = strtolower(trim($this->email));
        $user = User::where('email', $email)->first();
        if ($user) {
            // User exists, attach to site if not already
            if (!$this->site->users()->where('user_id', $user->id)->exists()) {
                $this->site->users()->attach($user->id);
                $this->reset(['email']);
                $this->site->refresh();
                $this->showInviteModal = false;
                $this->successMessage = null;
                Flux::toast(text: 'User added to site.', variant: 'success', duration: 3500);
            } else {
                $this->errorMessage = 'User already has access to this site.';
                // Keep modal open
            }
        } else {
            // User does not exist, create and send invite
            $user = User::create([
                'email' => $email,
                'name' => Str::before($email, '@'),
                'password' => null,
                'email_verified_at' => null,
            ]);
            $this->site->users()->attach($user->id);
            // Generate magic link token
            $token = Str::random(64);
            // Store invitation data in cache for redirect handling
            $magicLinkData = [
                'email' => $email,
                'remember' => true,
                'site_id' => $this->site->id,
                'is_invitation' => true
            ];
            cache()->put("magic_link_{$token}", $magicLinkData, now()->addMinutes(15));
            Mail::to($email)->queue(new MagicLinkMail($token, $this->site, true));
            $this->reset(['email']);
            $this->site->refresh();
            $this->showInviteModal = false;
            $this->successMessage = null;
            Flux::toast(text: 'Invitation sent!', variant: 'success', duration: 3500);
        }
    }

    public function confirmRemoveUser($userId)
    {
        $this->userIdToRemove = (int) $userId;
        $this->showRemoveModal = true;
    }

    public function cancelRemoveUser()
    {
        $this->showRemoveModal = false;
        $this->userIdToRemove = null;
    }

    public function removeUser()
    {
        $userId = $this->userIdToRemove;
        \Log::info('Attempting to detach user from site', [
            'site_id' => $this->site->id,
            'user_id' => $userId,
        ]);
        if ($userId) {
            \DB::enableQueryLog();
            $result = $this->site->users()->detach($userId);
            $queryLog = \DB::getQueryLog();
            \Log::info('Detach result', ['result' => $result, 'queryLog' => $queryLog]);
            if ($result === 0) {
                $deleted = \DB::table('site_user')
                    ->where('site_id', $this->site->id)
                    ->where('user_id', $userId)
                    ->delete();
                \Log::info('Direct DB delete result', ['deleted' => $deleted]);
                if ($deleted > 0) {
                    \Flux\Flux::toast(text: 'User access forcibly removed.', variant: 'warning', duration: 3500);
                } else {
                    \Flux\Flux::toast(text: 'Failed to remove user access.', variant: 'danger', duration: 3500);
                }
            } else {
                \Flux\Flux::toast(text: 'User access removed.', variant: 'success', duration: 3500);
            }
            $this->site->unsetRelation('users');
            $this->site = Site::with('users')->find($this->site->id);
        }
        $this->showRemoveModal = false;
        $this->userIdToRemove = null;
    }

    public function confirmResendInvitation($userId)
    {
        $this->userIdToResend = (int) $userId;
        $this->showResendModal = true;
    }

    public function cancelResendInvitation()
    {
        $this->showResendModal = false;
        $this->userIdToResend = null;
    }

    public function resendInvitation()
    {
        $userId = $this->userIdToResend;
        $user = User::find($userId);
        $cacheKey = 'site_resend_invite_' . $this->site->id . '_' . $userId;
        if (cache()->has($cacheKey)) {
            \Flux\Flux::toast(text: 'You can only re-send an invitation once per minute.', variant: 'danger', duration: 3500);
        } elseif ($user && is_null($user->email_verified_at) && is_null($user->password)) {
            $token = Str::random(64);
            // Store invitation data in cache for redirect handling
            $magicLinkData = [
                'email' => $user->email,
                'remember' => true,
                'site_id' => $this->site->id,
                'is_invitation' => true
            ];
            cache()->put("magic_link_{$token}", $magicLinkData, now()->addMinutes(15));
            Mail::to($user->email)->queue(new MagicLinkMail($token, $this->site, true));
            cache()->put($cacheKey, true, 60); // 1 minute
            \Flux\Flux::toast(text: 'Invitation re-sent!', variant: 'success', duration: 3500);
        } else {
            \Flux\Flux::toast(text: 'Cannot re-send invitation. User is not pending.', variant: 'danger', duration: 3500);
        }
        $this->showResendModal = false;
        $this->userIdToResend = null;
    }

    public function render()
    {
        $currentUserId = auth()->id();
        $users = $this->site->users->map(function ($user) use ($currentUserId) {
            $user->is_pending = is_null($user->email_verified_at) && is_null($user->password) && $user->id !== $currentUserId;
            return $user;
        });
        return view('livewire.site.users', [
            'users' => $users,
        ])->layout('components.layouts.site');
    }
}
