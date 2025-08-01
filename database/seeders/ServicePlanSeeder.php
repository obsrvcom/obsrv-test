<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\ServicePlanGroup;
use App\Models\ServicePlan;
use App\Models\ServicePlanFeatureCategory;
use App\Models\ServicePlanFeature;

class ServicePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company (you can modify this to seed for specific companies)
        $company = Company::first();
        if (!$company) {
            $this->command->info('No companies found. Please create a company first.');
            return;
        }

        $this->command->info("Seeding service plans for company: {$company->name}");

        // Create Complete Care Options group
        $planGroup = ServicePlanGroup::create([
            'company_id' => $company->id,
            'name' => 'Complete Care Options Comparison',
            'description' => 'Comprehensive support and maintenance plans with varying levels of coverage.',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Create service plans
        $plans = [
            [
                'name' => 'No Cover',
                'description' => 'Pay-as-you-go support with no monthly fees',
                'base_price_monthly' => null,
                'base_price_quarterly' => null,
                'base_price_annually' => null,
                'sort_order' => 0,
            ],
            [
                'name' => 'Level 1',
                'description' => 'Basic support coverage with remote diagnostics',
                'base_price_monthly' => 154.69,
                'base_price_quarterly' => 464.06,
                'base_price_annually' => 1856.25,
                'minimum_contract_months' => 12,
                'sort_order' => 1,
            ],
            [
                'name' => 'Level 2',
                'description' => 'Enhanced support with on-site coverage',
                'base_price_monthly' => 306.28,
                'base_price_quarterly' => 918.84,
                'base_price_annually' => 3675.38,
                'minimum_contract_months' => 12,
                'sort_order' => 2,
            ],
            [
                'name' => 'Level 3',
                'description' => 'Premium support with 24/7 coverage',
                'base_price_monthly' => 432.11,
                'base_price_quarterly' => 1296.33,
                'base_price_annually' => 5185.31,
                'minimum_contract_months' => 12,
                'sort_order' => 3,
            ],
            [
                'name' => 'Level 4',
                'description' => 'Ultimate support with fastest response times',
                'base_price_monthly' => 604.34,
                'base_price_quarterly' => 1813.03,
                'base_price_annually' => 7252.13,
                'minimum_contract_months' => 12,
                'is_featured' => true,
                'sort_order' => 4,
            ],
        ];

        $createdPlans = [];
        foreach ($plans as $planData) {
            $createdPlans[] = ServicePlan::create(array_merge($planData, [
                'service_plan_group_id' => $planGroup->id,
                'is_active' => true,
                'color' => '#3B82F6',
            ]));
        }

        // Create feature categories
        $categories = [
            [
                'name' => 'Telephone Support & Remote Diagnostics',
                'description' => 'Remote assistance and system diagnostics',
                'color' => '#3B82F6',
                'sort_order' => 0,
            ],
            [
                'name' => 'On Site Engineer Response',
                'description' => 'Physical on-site support and maintenance',
                'color' => '#10B981',
                'sort_order' => 1,
            ],
            [
                'name' => 'Labour Costs',
                'description' => 'Charges for technical work and callouts',
                'color' => '#F59E0B',
                'sort_order' => 2,
            ],
            [
                'name' => 'System Preventative Maintenance Bolt Ons',
                'description' => 'Additional maintenance services',
                'color' => '#8B5CF6',
                'sort_order' => 3,
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $createdCategories[] = ServicePlanFeatureCategory::create(array_merge($categoryData, [
                'company_id' => $company->id,
                'is_active' => true,
            ]));
        }

        // Create features with sample data
        $this->createSampleFeatures($createdCategories, $createdPlans);

        $this->command->info('Service plans seeded successfully!');
    }

    private function createSampleFeatures($categories, $plans)
    {
        // Telephone Support Features
        $feature1 = ServicePlanFeature::create([
            'service_plan_feature_category_id' => $categories[0]->id,
            'name' => 'Telephone & Remote System Support',
            'data_type' => 'boolean',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $values = [false, true, true, true, true];
        foreach ($plans as $index => $plan) {
            $plan->setFeatureValue($feature1, $values[$index], $values[$index]);
        }

        // Remote Response Time
        $feature2 = ServicePlanFeature::create([
            'service_plan_feature_category_id' => $categories[0]->id,
            'name' => 'Remote Login Response Time',
            'data_type' => 'text',
            'is_active' => true,
            'affects_sla' => true,
            'sort_order' => 1,
        ]);

        $responseValues = [
            'Up To 5 Working Days',
            'Same Day (Mon-Fri 8am - 5:30pm)',
            'Within 4 Hours (Mon-Fri 8am - 5:30pm)',
            'Within 4 Hours (24/7/365)',
            'Within 1 Hour (24/7/365)'
        ];

        foreach ($plans as $index => $plan) {
            $plan->setFeatureValue($feature2, $responseValues[$index], false, $responseValues[$index]);
        }

        // On-site Labour
        $feature3 = ServicePlanFeature::create([
            'service_plan_feature_category_id' => $categories[1]->id,
            'name' => 'All On-Site Labour Included',
            'data_type' => 'boolean',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $onsiteValues = [false, false, true, true, true];
        foreach ($plans as $index => $plan) {
            $plan->setFeatureValue($feature3, $onsiteValues[$index], $onsiteValues[$index]);
        }

        // Labour Costs
        $feature4 = ServicePlanFeature::create([
            'service_plan_feature_category_id' => $categories[2]->id,
            'name' => 'Remote Diagnostics (Cost)',
            'data_type' => 'currency',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $costs = [125.00, null, null, null, null];
        $included = [false, true, true, true, true];

        foreach ($plans as $index => $plan) {
            if ($costs[$index]) {
                $plan->setFeatureValue($feature4, $costs[$index], false, '£' . number_format($costs[$index], 2));
            } else {
                $plan->setFeatureValue($feature4, null, $included[$index], $included[$index] ? 'Included' : '-');
            }
        }
    }
}
