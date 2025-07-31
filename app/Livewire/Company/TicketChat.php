<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Site;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class TicketChat extends Component
{
    public $site;
    public $company;

    public function mount($company, $site)
    {
        $this->company = is_string($company) ? Company::findOrFail($company) : $company;
        $this->site = is_string($site) ? Site::findOrFail($site) : $site;
    }

    public function render()
    {
        return view('livewire.company.ticket-chat')
            ->layout('components.layouts.company');
    }
}
