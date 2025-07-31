<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Internet extends Component
{
    public function render()
    {
        return view('livewire.site.internet')
            ->layout('components.layouts.site');
    }
}
