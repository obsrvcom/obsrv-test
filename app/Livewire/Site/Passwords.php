<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Passwords extends Component
{
    public function render()
    {
        return view('livewire.site.passwords')
            ->layout('components.layouts.site');
    }
}
