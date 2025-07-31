<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Monitoring extends Component
{
    public function render()
    {
        return view('livewire.site.monitoring')
            ->layout('components.layouts.site');
    }
}
