<?php

namespace App\Livewire\Site;

use Livewire\Component;

class Quotations extends Component
{
    public function render()
    {
        return view('livewire.site.quotations')
            ->layout('components.layouts.site');
    }
}
