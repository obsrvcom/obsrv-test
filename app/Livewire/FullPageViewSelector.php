<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class FullPageViewSelector extends Component
{
    public $userCompanies;
    public $userSites;

            public function mount()
    {
        $user = Auth::user();

        // Always show the selector page when explicitly navigated to /select
        // Users who click "View All Workspaces" want to see their options
        $this->userCompanies = $user->companies()->get();
        $this->userSites = $user->accessibleSites();
    }

    public function selectCompany($companyId)
    {
        // Redirect to the selected company's dashboard
        return redirect()->route('company.dashboard', ['company' => $companyId]);
    }

    public function selectSite($siteId)
    {
        // Redirect to the selected site's dashboard
        return redirect()->route('site.dashboard', ['site' => $siteId]);
    }

    public function render()
    {
        return view('livewire.full-page-view-selector', [
            'userCompanies' => $this->userCompanies,
            'userSites' => $this->userSites,
        ])->layout('components.layouts.auth');
    }
}
