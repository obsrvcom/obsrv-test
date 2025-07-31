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
        $companies = $user->companies()->with('users')->get();

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

        // Redirect to the new company dashboard route
        return redirect()->route('company.dashboard', ['company' => $company->id]);
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
            'request_data' => $request->only(['name', 'description'])
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Rate limit company creation by user and IP
        $maxAttempts = 3;
        $decayMinutes = 10;
        $key = 'company-create:' . $request->ip() . ':' . Auth::id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return back()->with('error', 'Too many company creation attempts. Please try again later.');
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, $decayMinutes * 60);

        \Log::info('Company creation validation passed');

        $company = Company::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        \Log::info('Company created', [
            'company_id' => $company->id,
            'company_name' => $company->name
        ]);

        // Add the current user as owner
        $company->users()->attach(Auth::id(), ['role' => 'owner']);

        \Log::info('User attached to company as owner', [
            'user_id' => Auth::id(),
            'company_id' => $company->id
        ]);

        // Set as current company
        session(['current_company_id' => $company->id]);

        // Redirect to the new company dashboard route
        return redirect()->route('company.dashboard', ['company' => $company->id])->with('success', 'Company created successfully!');
    }

    public function show($companyId)
    {
        $company = Company::findOrFail($companyId);
        return view('company.show', compact('company'));
    }
}
