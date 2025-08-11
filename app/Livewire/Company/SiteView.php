<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Company;
use App\Models\Site;

class SiteView extends Component
{
    public Company $company;
    public Site $site;

    public function mount($company = null, $site = null)
    {
        // Handle route model binding
        if ($company instanceof Company) {
            $this->company = $company;
        } else {
            // Fallback to getting company from route or current company
            $routeCompany = request()->route('company');
            if ($routeCompany instanceof Company) {
                $this->company = $routeCompany;
            } else {
                $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
            }
        }

        if ($site instanceof Site) {
            $this->site = $site;
        } else {
            // Get site from route parameter
            $routeSite = request()->route('site');
            if ($routeSite instanceof Site) {
                $this->site = $routeSite;
            } else {
                abort(404);
            }
        }

        // Ensure the site belongs to the company
        if ($this->site->company_id !== $this->company->id) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.company.site-view')
            ->layout('components.layouts.company');
    }
}