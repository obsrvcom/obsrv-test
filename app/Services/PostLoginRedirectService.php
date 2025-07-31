<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PostLoginRedirectService
{
    /**
     * Determine where to redirect user after login based on their access
     *
     * @param User|null $user
     * @param string|null $preferredRedirect - Specific redirect URL (e.g. from invitation)
     * @return string
     */
    public static function getPostLoginRedirect(?User $user = null, ?string $preferredRedirect = null): string
    {
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user) {
            return route('login');
        }

        // If there's a preferred redirect (like from invitation), use it
        if ($preferredRedirect) {
            return $preferredRedirect;
        }

        // Get user's companies and sites
        $companies = $user->companies()->get();
        $allSites = $user->accessibleSites();

        // Get only direct site access (not through companies)
        $directSites = $user->sites()->get();

        $companyCount = $companies->count();
        $directSiteCount = $directSites->count();

        // If user has access to only 1 company and no direct sites, go to that company
        if ($companyCount === 1 && $directSiteCount === 0) {
            $company = $companies->first();
            return route('company.dashboard', ['company' => $company->id]);
        }

        // If user has access to only 1 direct site and no companies, go to that site
        if ($companyCount === 0 && $directSiteCount === 1) {
            $site = $directSites->first();
            return route('site.dashboard', ['site' => $site->id]);
        }

        // If user has access to only 1 company (even with sites through company), prioritize company
        if ($companyCount === 1 && $directSiteCount === 0) {
            $company = $companies->first();
            return route('company.dashboard', ['company' => $company->id]);
        }

        // If user has multiple companies/sites or mixed access, show selector
        if ($companyCount > 1 || $directSiteCount > 1 || ($companyCount >= 1 && $directSiteCount >= 1)) {
            return route('view.select');
        }

        // If user has no access to anything, redirect to company selection/creation
        return route('company.select');
    }

    /**
     * Handle immediate redirect after login
     *
     * @param User|null $user
     * @param string|null $preferredRedirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function redirectAfterLogin(?User $user = null, ?string $preferredRedirect = null)
    {
        $url = self::getPostLoginRedirect($user, $preferredRedirect);
        return redirect($url);
    }

    /**
     * Extract redirect URL from magic link invitation data
     *
     * @param array $magicLinkData
     * @return string|null
     */
    public static function extractInvitationRedirect(array $magicLinkData): ?string
    {
        // Check if this is a site invitation
        if (isset($magicLinkData['site_id'])) {
            return route('site.dashboard', ['site' => $magicLinkData['site_id']]);
        }

        // Check if this is a company invitation
        if (isset($magicLinkData['company_id'])) {
            return route('company.dashboard', ['company' => $magicLinkData['company_id']]);
        }

        return null;
    }
}
