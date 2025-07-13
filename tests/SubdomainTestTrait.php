<?php

namespace Tests;

use App\Models\Company;
use App\Models\User;
use App\Http\Middleware\HandleCompanySubdomain;

trait SubdomainTestTrait
{
    protected function setupSubdomainTest(User $user, Company $company = null)
    {
        if (!$company) {
            $company = Company::factory()->create();
        }

        // Attach user to company
        $user->companies()->attach($company->id, ['role' => 'admin']);

        // Set current company in session
        session(['current_company_id' => $company->id]);

        // Mock the request to simulate subdomain
        $this->withHeaders([
            'Host' => $company->subdomain . '.' . parse_url(config('app.url'), PHP_URL_HOST)
        ]);

        return $company;
    }

    protected function setupMainDomainTest()
    {
        // Mock the request to simulate main domain
        $this->withHeaders([
            'Host' => parse_url(config('app.url'), PHP_URL_HOST)
        ]);
    }

    protected function withoutSubdomainMiddleware()
    {
        // Disable the HandleCompanySubdomain middleware for testing
        $this->withoutMiddleware(HandleCompanySubdomain::class);
    }

    protected function setupTestWithCompany(User $user, Company $company = null)
    {
        if (!$company) {
            $company = Company::factory()->create();
        }

        // Attach user to company
        $user->companies()->attach($company->id, ['role' => 'admin']);

        // Set current company in session
        session(['current_company_id' => $company->id]);

        // Disable subdomain middleware for testing
        $this->withoutSubdomainMiddleware();

        return $company;
    }
}
