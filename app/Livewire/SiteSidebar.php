<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Site;

class SiteSidebar extends Component
{
    public $site;
    public $siteId;
    public $companyName;

    public function mount()
    {
        $this->site = request()->route('site');

        // Handle case where route parameter might be string ID
        if (is_string($this->site)) {
            $this->site = Site::find($this->site);
        }

        $this->siteId = $this->site ? $this->site->id : null;
        $this->companyName = $this->site && $this->site->company ? $this->site->company->name : null;
    }

    public function render()
    {
        return view('livewire.site-sidebar');
    }
}
