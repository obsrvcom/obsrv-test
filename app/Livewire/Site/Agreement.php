<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Agreement extends Component
{
    public function render()
    {
        return view('livewire.site.agreement')
            ->layout('components.layouts.site');
    }
}
