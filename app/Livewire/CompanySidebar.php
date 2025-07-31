<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Company;

class CompanySidebar extends Component
{
    public $company;

    public function mount()
    {
        $this->company = request()->route('company');

        // Handle case where route parameter might be string ID
        if (is_string($this->company)) {
            $this->company = Company::find($this->company);
        }

        // Fallback to user's current company
        if (!$this->company) {
            $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
        }
    }

    public function render()
    {
        return view('livewire.company-sidebar');
    }
}
