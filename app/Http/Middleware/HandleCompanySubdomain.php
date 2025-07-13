<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class HandleCompanySubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for magic link routes
        if (str_starts_with($request->path(), 'magic-link/')) {
            return $next($request);
        }

        // Get subdomain and base domain from host
        $host = $request->getHost();
        $domainInfo = $this->parseDomain($host);

        \Log::info('HandleCompanySubdomain middleware', [
            'host' => $host,
            'subdomain' => $domainInfo['subdomain'],
            'base_domain' => $domainInfo['base_domain'],
            'path' => $request->path(),
            'is_app_route' => $this->isAppRoute($request->path())
        ]);

        // If no subdomain, this is the main site
        if (!$domainInfo['subdomain'] || $domainInfo['subdomain'] === 'www') {
            \Log::info('Allowing request to continue on main domain', [
                'auth_check' => Auth::check(),
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'session_cookie_name' => config('session.cookie'),
                'session_domain' => config('session.domain')
            ]);
            // If trying to access app routes on main domain, redirect to home
            if ($this->isAppRoute($request->path())) {
                return redirect()->route('home');
            }
            return $next($request);
        }

        // This is a subdomain - find company
        $company = Company::where('subdomain', $domainInfo['subdomain'])->first();

        if (!$company) {
            // Company not found - redirect to company selection or show error
            if (Auth::check()) {
                return redirect()->route('company.select');
            } else {
                // For unauthenticated users, redirect to main domain login
                return redirect()->away("http://{$domainInfo['base_domain']}/login");
            }
        }

        // Store company in request for later use
        $request->attributes->set('current_company', $company);

        // If this is the root path of the subdomain, handle redirects
        if ($request->path() === '/') {
            if (Auth::check()) {
                $user = Auth::user();

                // Check if user has access to this company
                if (!$company->hasUser($user)) {
                    return redirect()->route('company.select')->with('error', 'You do not have access to this company.');
                }

                // Set current company in session
                session(['current_company_id' => $company->id]);

                // Redirect to dashboard
                return redirect()->route('dashboard');
            } else {
                // User not authenticated - redirect to login on main domain
                return redirect()->away("http://{$domainInfo['base_domain']}/login");
            }
        }

        // For non-root paths, check authentication and access
        if (Auth::check()) {
            $user = Auth::user();

            if (!$company->hasUser($user)) {
                // User doesn't have access to this company
                return redirect()->route('company.select')->with('error', 'You do not have access to this company.');
            }

            // Set current company in session
            session(['current_company_id' => $company->id]);
        } else {
            // User not authenticated on subdomain - redirect to login for app routes
            if ($this->isAppRoute($request->path())) {
                return redirect()->away("http://{$domainInfo['base_domain']}/login");
            }
        }

        // Redirect company selection routes from subdomains to main domain
        // But allow company settings routes on subdomains
        if (str_starts_with($request->path(), 'company/select') ||
            str_starts_with($request->path(), 'company/create') ||
            str_starts_with($request->path(), 'company/store') ||
            str_starts_with($request->path(), 'company/switch')) {
            return redirect()->away("http://{$domainInfo['base_domain']}/{$request->path()}");
        }

        return $next($request);
    }

        /**
     * Parse domain to extract subdomain and base domain
     * Uses the configured app domain from environment
     */
    private function parseDomain($host)
    {
        // Get the configured app domain from environment
        $appUrl = config('app.url');

        // Extract domain from app.url
        $appDomain = parse_url($appUrl, PHP_URL_HOST);

        // If the host matches the app domain exactly, no subdomain
        if ($host === $appDomain) {
            return [
                'subdomain' => null,
                'base_domain' => $appDomain
            ];
        }

        // Check if the host starts with a subdomain followed by the app domain
        $subdomainPattern = '/^([^.]+)\.' . preg_quote($appDomain, '/') . '$/';
        if (preg_match($subdomainPattern, $host, $matches)) {
            return [
                'subdomain' => $matches[1],
                'base_domain' => $appDomain
            ];
        }

        // If no match, return the host as base domain with no subdomain
        return [
            'subdomain' => null,
            'base_domain' => $host
        ];
    }

    private function isAppRoute($path)
    {
        $appRoutes = [
            'dashboard',
            'user-settings',
            'company-settings',
        ];

        // Company selection routes should only be accessible on main domain
        if (str_starts_with($path, 'company/select') ||
            str_starts_with($path, 'company/create') ||
            str_starts_with($path, 'company/store') ||
            str_starts_with($path, 'company/switch')) {
            return false; // Don't redirect company selection routes on main domain
        }

        foreach ($appRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }

        return false;
    }
}
