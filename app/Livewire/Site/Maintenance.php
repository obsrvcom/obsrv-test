<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Maintenance extends Component
{
    public function render()
    {
        return view('livewire.site.maintenance')
            ->layout('components.layouts.site');
    }
}
