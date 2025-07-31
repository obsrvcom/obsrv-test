<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;

class TicketChat extends Component
{
    public $site;

    public function mount($site)
    {
        $this->site = is_string($site) ? Site::findOrFail($site) : $site;
    }

    public function render()
    {
        return view('livewire.site.ticket-chat')
            ->layout('components.layouts.site');
    }
}
