<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class EnsureCompanyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $companyParam = $request->route('company');

        // Handle if company is already a model instance or if it's an ID
        if ($companyParam instanceof Company) {
            $company = $companyParam;
        } else {
            $company = Company::find($companyParam);
        }

        if (!$company || !$company->hasUser($user)) {
            return redirect()->route('company.select')->with('error', 'You do not have access to this company.');
        }

        // Optionally, set current company in session/context
        session(['current_company_id' => $company->id]);
        $request->attributes->set('current_company', $company);

        return $next($request);
    }
}
