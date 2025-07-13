<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $company = $user->currentCompanyFromRequest() ?? $user->currentCompany();

            if (!$company) {
                return redirect()->route('company.select');
            }

            // Check if user is admin or owner
            if (!$company->isUserAdmin($user)) {
                return redirect()->route('dashboard')->with('error', 'You do not have permission to access company settings.');
            }
        }

        return $next($request);
    }
}
