<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Settings extends Component
{
    public function render()
    {
        return view('livewire.site.settings')
            ->layout('components.layouts.site');
    }
}
