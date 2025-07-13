<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample companies
        $companies = [
            [
                'name' => 'Acme Corporation',
                'subdomain' => 'acme',
                'description' => 'A leading technology company specializing in innovative solutions.',
            ],
            [
                'name' => 'TechStart Inc',
                'subdomain' => 'techstart',
                'description' => 'Startup focused on cutting-edge software development.',
            ],
            [
                'name' => 'Global Solutions',
                'subdomain' => 'global',
                'description' => 'International consulting and business solutions provider.',
            ],
        ];

        foreach ($companies as $companyData) {
            $company = Company::create($companyData);

            // Add the first user (if exists) as owner
            $user = User::first();
            if ($user) {
                $company->users()->attach($user->id, ['role' => 'owner']);
            }
        }
    }
}
