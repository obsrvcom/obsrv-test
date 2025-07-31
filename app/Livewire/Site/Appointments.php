<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Appointments extends Component
{
    public function render()
    {
        return view('livewire.site.appointments')
            ->layout('components.layouts.site');
    }
}
