<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PlanCategory;
use App\Models\ServicePlanNew;
use App\Models\ServicePlanFeatureGroupNew;
use Illuminate\Database\Seeder;

class PlanCategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();

        foreach ($companies as $company) {
            // Create default "Service" category for each company
            $serviceCategory = PlanCategory::create([
                'company_id' => $company->id,
                'name' => 'Service',
                'description' => 'Service plans and maintenance packages',
                'slug' => 'service',
                'is_active' => true,
                'sort_order' => 0,
                'color' => '#3B82F6',
                'icon' => 'wrench-screwdriver',
            ]);

            // Update existing service plans to belong to the service category
            ServicePlanNew::where('company_id', $company->id)
                ->whereNull('category_id')
                ->update(['category_id' => $serviceCategory->id]);

            // Update existing feature groups to belong to the service category
            ServicePlanFeatureGroupNew::where('company_id', $company->id)
                ->whereNull('category_id')
                ->update(['category_id' => $serviceCategory->id]);

            // Create additional example categories
            PlanCategory::create([
                'company_id' => $company->id,
                'name' => 'Product',
                'description' => 'Product plans and subscriptions',
                'slug' => 'product',
                'is_active' => true,
                'sort_order' => 1,
                'color' => '#10B981',
                'icon' => 'cube',
            ]);

            PlanCategory::create([
                'company_id' => $company->id,
                'name' => 'Maintenance',
                'description' => 'Maintenance and support packages',
                'slug' => 'maintenance',
                'is_active' => true,
                'sort_order' => 2,
                'color' => '#F59E0B',
                'icon' => 'cog-6-tooth',
            ]);
        }
    }
}