<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    public function checkSubdomain(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string|max:255|regex:/^[a-z0-9]+$/',
        ]);

        $subdomain = $request->subdomain;

        // Check if subdomain already exists
        $exists = Company::where('subdomain', $subdomain)->exists();

        return response()->json([
            'available' => !$exists,
            'subdomain' => $subdomain,
        ]);
    }
}
