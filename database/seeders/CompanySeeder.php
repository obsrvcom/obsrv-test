<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample companies
        DB::table('companies')->insert([
            [
                'name' => 'Acme',
                'description' => 'Acme description',
            ],
            [
                'name' => 'TechStart',
                'description' => 'TechStart description',
            ],
            [
                'name' => 'Global',
                'description' => 'Global description',
            ],
        ]);

        // Add the first user (if exists) as owner
        $user = User::first();
        if ($user) {
            $company = Company::first(); // Assuming the first company created is the one to attach the user to
            if ($company) {
                $company->users()->attach($user->id, ['role' => 'owner']);
            }
        }
    }
}
