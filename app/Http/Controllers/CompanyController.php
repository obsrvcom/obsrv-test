<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class CompanyController extends Controller
{
    public function select()
    {
        \Log::info('Company selection page accessed', [
            'user_id' => Auth::id(),
            'auth_check' => Auth::check(),
            'session_id' => session()->getId()
        ]);

        $user = Auth::user();
        $companies = $user->companies;

        // If user has no companies, redirect to create one
        if ($companies->count() === 0) {
            \Log::info('User has no companies, redirecting to create');
            return redirect()->route('company.create');
        }

        \Log::info('Showing company selection page', [
            'user_id' => $user->id,
            'companies_count' => $companies->count()
        ]);

        return view('company.select', compact('companies'));
    }

    public function switch(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id'
        ]);

        $user = Auth::user();
        $company = Company::findOrFail($request->company_id);

        // Check if user has access to this company
        if (!$company->hasUser($user)) {
            return back()->with('error', 'You do not have access to this company.');
        }

        // Set current company in session
        session(['current_company_id' => $company->id]);

        // Redirect to the subdomain dashboard
        $host = request()->getHost();
        $domain = $this->extractDomain($host);
        return redirect()->away("http://{$company->subdomain}.{$domain}/dashboard");
    }

    public function create()
    {
        \Log::info('Company create page accessed', [
            'user_id' => Auth::id(),
            'auth_check' => Auth::check(),
            'session_id' => session()->getId()
        ]);

        return view('company.create');
    }

    public function store(Request $request)
    {
        \Log::info('Company creation started', [
            'user_id' => Auth::id(),
            'request_data' => $request->only(['name', 'subdomain', 'description'])
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:255|unique:companies,subdomain|regex:/^[a-z0-9]+$/',
            'description' => 'nullable|string',
        ]);

        \Log::info('Company creation validation passed');

        $company = Company::create([
            'name' => $request->name,
            'subdomain' => $request->subdomain,
            'description' => $request->description,
        ]);

        \Log::info('Company created', [
            'company_id' => $company->id,
            'company_name' => $company->name,
            'subdomain' => $company->subdomain
        ]);

        // Add the current user as owner
        $company->users()->attach(Auth::id(), ['role' => 'owner']);

        \Log::info('User attached to company as owner', [
            'user_id' => Auth::id(),
            'company_id' => $company->id
        ]);

        // Set as current company
        session(['current_company_id' => $company->id]);

        // Redirect to the subdomain dashboard
        $host = request()->getHost();
        $domain = $this->extractDomain($host);
        $redirectUrl = "http://{$company->subdomain}.{$domain}/dashboard";

        \Log::info('Redirecting to company dashboard', [
            'redirect_url' => $redirectUrl,
            'host' => $host,
            'domain' => $domain,
            'subdomain' => $company->subdomain
        ]);

        return redirect()->away($redirectUrl)->with('success', 'Company created successfully!');
    }

    private function extractDomain($host)
    {
        // Extract domain (everything after the first dot)
        $parts = explode('.', $host);

        if (count($parts) <= 2) {
            return $host; // No subdomain, return full host
        }

        // Remove subdomain and return domain
        array_shift($parts);
        return implode('.', $parts);
    }
}
