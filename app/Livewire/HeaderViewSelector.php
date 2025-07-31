<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class HeaderViewSelector extends Component
{
    public $currentCompany;
    public $userCompanies;
    public $userSites;
    public $currentSite;

    public function mount()
    {
        $user = Auth::user();
        $request = request();
        $this->currentSite = $request->attributes->get('current_site');

        // Get company from route model binding first (for company context pages)
        $routeCompany = $request->route('company');
        if ($routeCompany && is_object($routeCompany)) {
            $this->currentCompany = $routeCompany;
        } else {
            // Fallback to user's current company
            $this->currentCompany = $user->currentCompanyFromRequest() ?? $user->currentCompany();
        }

        $this->userCompanies = $user->companies;
        // Show all sites the user has access to, even if not a company member
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

    #[On('company-avatar-updated')]
    public function refreshCompanyData($companyId = null)
    {
        // Refresh the current company data to get updated avatar
        if ($this->currentCompany && ($companyId === null || $this->currentCompany->id == $companyId)) {
            $this->currentCompany = $this->currentCompany->fresh();
        }

        // If we're viewing a site, refresh the site's company data too
        if ($this->currentSite && $this->currentSite->company_id == $companyId) {
            $this->currentSite = $this->currentSite->fresh(['company']);
        }

        // Refresh user companies collection
        $this->userCompanies = auth()->user()->companies;
    }

    public function render()
    {
        return view('livewire.header-view-selector', [
            'userCompanies' => $this->userCompanies,
            'userSites' => $this->userSites,
            'currentCompany' => $this->currentCompany,
            'currentSite' => $this->currentSite,
        ]);
    }
}
