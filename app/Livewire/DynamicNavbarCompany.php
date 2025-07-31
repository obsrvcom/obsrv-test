<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DynamicNavbarCompany extends Component
{
    public $isUserSettings;
    public $isCompanySettings;
    public $isMonitoring;
    public $currentCompany;

    public function mount()
    {
        $request = request();
        $user = Auth::user();
        $this->isUserSettings = $request->routeIs('settings.*') || str_starts_with($request->path(), 'user-settings/');
        $this->isCompanySettings = ($request->routeIs('company.settings*') && !$request->routeIs('company.billing')) || str_starts_with($request->path(), 'company-settings/');
        $this->isMonitoring = $request->routeIs('monitoring');

        // Get company from route model binding first (for company context pages)
        $routeCompany = $request->route('company');
        if ($routeCompany && is_object($routeCompany)) {
            $this->currentCompany = $routeCompany;
        } else {
            // Fallback to user's current company
            $this->currentCompany = $user ? ($user->currentCompanyFromRequest() ?? $user->currentCompany()) : null;
        }
    }

    public function render()
    {
        return view('livewire.app.dynamic-navbar-company');
    }
}
