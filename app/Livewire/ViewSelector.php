<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ViewSelector extends Component
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

    public function render()
    {
        $appUrl = config('app.url');
        $appDomain = parse_url($appUrl, PHP_URL_HOST);
        return view('livewire.app.company.view-selector', [
            'appDomain' => $appDomain,
            'userCompanies' => $this->userCompanies,
            'userSites' => $this->userSites,
            'currentCompany' => $this->currentCompany,
            'currentSite' => $this->currentSite,
        ]);
    }
}
