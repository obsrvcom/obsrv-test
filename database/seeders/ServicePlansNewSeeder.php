<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\ServicePlanNew;
use App\Models\ServicePlanRevision;
use App\Models\ServicePlanLevel;
use App\Models\ServicePlanFeatureGroupNew;
use App\Models\ServicePlanFeatureNew;
use App\Models\ServicePlanLevelFeatureValue;

class ServicePlansNewSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first company (assuming one exists)
        $company = Company::first();

        if (!$company) {
            $this->command->info('No company found. Please create a company first.');
            return;
        }

        $this->command->info('Creating service plans for: ' . $company->name);

        // Create Feature Groups
        $telephoneSupport = $company->servicePlanFeatureGroupsNew()->create([
            'name' => 'Telephone Support & Remote Diagnostics',
            'description' => 'Remote support and diagnostic services',
            'color' => '#3B82F6',
            'sort_order' => 1,
        ]);

        $onsiteResponse = $company->servicePlanFeatureGroupsNew()->create([
            'name' => 'On-Site Response',
            'description' => 'Physical on-site service responses',
            'color' => '#10B981',
            'sort_order' => 2,
        ]);

        $labourCosts = $company->servicePlanFeatureGroupsNew()->create([
            'name' => 'Labour Costs',
            'description' => 'Coverage for service labor and technician time',
            'color' => '#F59E0B',
            'sort_order' => 3,
        ]);

        $maintenance = $company->servicePlanFeatureGroupsNew()->create([
            'name' => 'Maintenance',
            'description' => 'Regular maintenance and upkeep services',
            'color' => '#8B5CF6',
            'sort_order' => 4,
        ]);

        // Create Features for Telephone Support
        $remoteLoginResponse = $telephoneSupport->features()->create([
            'name' => 'Remote Login Response Time',
            'description' => 'Maximum time to respond to remote login requests',
            'data_type' => 'time',
            'unit' => 'hours',
            'affects_sla' => true,
            'sort_order' => 1,
        ]);

        $diagnosticsResponse = $telephoneSupport->features()->create([
            'name' => 'Remote Diagnostics Response Time',
            'description' => 'Maximum time to respond to diagnostic requests',
            'data_type' => 'time',
            'unit' => 'hours',
            'affects_sla' => true,
            'sort_order' => 2,
        ]);

        $telephoneSupport24x7 = $telephoneSupport->features()->create([
            'name' => '24x7 Telephone Support',
            'description' => 'Round-the-clock telephone support availability',
            'data_type' => 'boolean',
            'affects_sla' => false,
            'sort_order' => 3,
        ]);

        // Create Features for On-Site Response
        $onsiteResponseTime = $onsiteResponse->features()->create([
            'name' => 'On-Site Response Time',
            'description' => 'Maximum time to arrive on-site for service calls',
            'data_type' => 'time',
            'unit' => 'hours',
            'affects_sla' => true,
            'sort_order' => 1,
        ]);

        $emergencyCallout = $onsiteResponse->features()->create([
            'name' => 'Emergency Call-out',
            'description' => 'Emergency on-site response availability',
            'data_type' => 'boolean',
            'affects_sla' => true,
            'sort_order' => 2,
        ]);

        // Create Features for Labour Costs
        $labourCoverage = $labourCosts->features()->create([
            'name' => 'Labour Coverage',
            'description' => 'Percentage of labour costs covered',
            'data_type' => 'number',
            'unit' => '%',
            'affects_sla' => false,
            'sort_order' => 1,
        ]);

        $calloutFee = $labourCosts->features()->create([
            'name' => 'Call-out Fee Coverage',
            'description' => 'Coverage for technician call-out fees',
            'data_type' => 'boolean',
            'affects_sla' => false,
            'sort_order' => 2,
        ]);

        // Create Features for Maintenance
        $annualMaintenance = $maintenance->features()->create([
            'name' => 'Annual Maintenance Visits',
            'description' => 'Number of scheduled maintenance visits per year',
            'data_type' => 'number',
            'unit' => 'visits',
            'affects_sla' => false,
            'sort_order' => 1,
        ]);

        $priorityParts = $maintenance->features()->create([
            'name' => 'Priority Parts Ordering',
            'description' => 'Priority ordering and delivery of replacement parts',
            'data_type' => 'boolean',
            'affects_sla' => false,
            'sort_order' => 2,
        ]);

        // Create Service Plan
        $servicePlan = $company->servicePlansNew()->create([
            'name' => 'Complete Care 2025',
            'description' => 'Comprehensive IT support and maintenance service plans for 2025',
            'color' => '#3B82F6',
            'sort_order' => 1,
        ]);

        // Create Published Revision
        $publishedRevision = $servicePlan->revisions()->create([
            'name' => 'v1.0 Published',
            'description' => 'Initial published version for 2025',
            'status' => 'published',
            'version_number' => 1,
            'is_current' => true,
            'published_at' => now(),
        ]);

        // Create Draft Revision
        $draftRevision = $servicePlan->revisions()->create([
            'name' => 'v1.1 Draft',
            'description' => 'Updated pricing and features for Q2 2025',
            'status' => 'draft',
            'version_number' => 2,
            'is_current' => false,
        ]);

        // Create Levels for Published Revision
        $noCover = $publishedRevision->levels()->create([
            'name' => 'No Cover',
            'description' => 'Basic support with no additional coverage',
            'monthly_price' => 0.00,
            'quarterly_price' => 0.00,
            'annual_price' => 0.00,
            'color' => '#6B7280',
            'sort_order' => 0,
        ]);

        $level1 = $publishedRevision->levels()->create([
            'name' => 'Level 1',
            'description' => 'Basic support with limited response times',
            'monthly_price' => 49.99,
            'quarterly_price' => 139.99,
            'annual_price' => 499.99,
            'minimum_contract_months' => 12,
            'color' => '#10B981',
            'sort_order' => 1,
        ]);

        $level2 = $publishedRevision->levels()->create([
            'name' => 'Level 2',
            'description' => 'Enhanced support with faster response times',
            'monthly_price' => 99.99,
            'quarterly_price' => 279.99,
            'annual_price' => 999.99,
            'minimum_contract_months' => 12,
            'color' => '#3B82F6',
            'is_featured' => true,
            'sort_order' => 2,
        ]);

        $level3 = $publishedRevision->levels()->create([
            'name' => 'Level 3',
            'description' => 'Premium support with comprehensive coverage',
            'monthly_price' => 149.99,
            'quarterly_price' => 419.99,
            'annual_price' => 1499.99,
            'minimum_contract_months' => 12,
            'color' => '#F59E0B',
            'sort_order' => 3,
        ]);

        $level4 = $publishedRevision->levels()->create([
            'name' => 'Level 4',
            'description' => 'Enterprise support with maximum coverage and priority',
            'monthly_price' => 249.99,
            'quarterly_price' => 699.99,
            'annual_price' => 2499.99,
            'minimum_contract_months' => 24,
            'color' => '#8B5CF6',
            'sort_order' => 4,
        ]);

        // Assign feature groups to levels and set values
        $levels = [$noCover, $level1, $level2, $level3, $level4];
        $featureGroups = [$telephoneSupport, $onsiteResponse, $labourCosts, $maintenance];

        foreach ($levels as $level) {
            foreach ($featureGroups as $group) {
                $level->attachFeatureGroup($group, true, $group->sort_order);
            }
        }

        // Set feature values for each level
        $this->setFeatureValues($noCover, [
            $remoteLoginResponse->id => ['value' => null, 'is_included' => false],
            $diagnosticsResponse->id => ['value' => null, 'is_included' => false],
            $telephoneSupport24x7->id => ['value' => null, 'is_included' => false],
            $onsiteResponseTime->id => ['value' => null, 'is_included' => false],
            $emergencyCallout->id => ['value' => null, 'is_included' => false],
            $labourCoverage->id => ['value' => '0', 'display_value' => '0%'],
            $calloutFee->id => ['value' => null, 'is_included' => false],
            $annualMaintenance->id => ['value' => '0', 'display_value' => '0 visits'],
            $priorityParts->id => ['value' => null, 'is_included' => false],
        ]);

        $this->setFeatureValues($level1, [
            $remoteLoginResponse->id => ['value' => '24', 'display_value' => '24 hours'],
            $diagnosticsResponse->id => ['value' => '48', 'display_value' => '48 hours'],
            $telephoneSupport24x7->id => ['value' => null, 'is_included' => false],
            $onsiteResponseTime->id => ['value' => '72', 'display_value' => '72 hours'],
            $emergencyCallout->id => ['value' => null, 'is_included' => false],
            $labourCoverage->id => ['value' => '50', 'display_value' => '50%'],
            $calloutFee->id => ['value' => null, 'is_included' => false],
            $annualMaintenance->id => ['value' => '1', 'display_value' => '1 visit'],
            $priorityParts->id => ['value' => null, 'is_included' => false],
        ]);

        $this->setFeatureValues($level2, [
            $remoteLoginResponse->id => ['value' => '8', 'display_value' => '8 hours'],
            $diagnosticsResponse->id => ['value' => '24', 'display_value' => '24 hours'],
            $telephoneSupport24x7->id => ['value' => null, 'is_included' => true],
            $onsiteResponseTime->id => ['value' => '48', 'display_value' => '48 hours'],
            $emergencyCallout->id => ['value' => null, 'is_included' => false],
            $labourCoverage->id => ['value' => '75', 'display_value' => '75%'],
            $calloutFee->id => ['value' => null, 'is_included' => true],
            $annualMaintenance->id => ['value' => '2', 'display_value' => '2 visits'],
            $priorityParts->id => ['value' => null, 'is_included' => false],
        ]);

        $this->setFeatureValues($level3, [
            $remoteLoginResponse->id => ['value' => '4', 'display_value' => '4 hours'],
            $diagnosticsResponse->id => ['value' => '8', 'display_value' => '8 hours'],
            $telephoneSupport24x7->id => ['value' => null, 'is_included' => true],
            $onsiteResponseTime->id => ['value' => '24', 'display_value' => '24 hours'],
            $emergencyCallout->id => ['value' => null, 'is_included' => true],
            $labourCoverage->id => ['value' => '90', 'display_value' => '90%'],
            $calloutFee->id => ['value' => null, 'is_included' => true],
            $annualMaintenance->id => ['value' => '4', 'display_value' => '4 visits'],
            $priorityParts->id => ['value' => null, 'is_included' => true],
        ]);

        $this->setFeatureValues($level4, [
            $remoteLoginResponse->id => ['value' => '2', 'display_value' => '2 hours'],
            $diagnosticsResponse->id => ['value' => '4', 'display_value' => '4 hours'],
            $telephoneSupport24x7->id => ['value' => null, 'is_included' => true],
            $onsiteResponseTime->id => ['value' => '8', 'display_value' => '8 hours'],
            $emergencyCallout->id => ['value' => null, 'is_included' => true],
            $labourCoverage->id => ['value' => '100', 'display_value' => '100%'],
            $calloutFee->id => ['value' => null, 'is_included' => true],
            $annualMaintenance->id => ['value' => '6', 'display_value' => '6 visits'],
            $priorityParts->id => ['value' => null, 'is_included' => true],
        ]);

        $this->command->info('✅ Service plans seeded successfully!');
        $this->command->info('📋 Created:');
        $this->command->info('   • 1 Service Plan: ' . $servicePlan->name);
        $this->command->info('   • 2 Revisions: Published v1.0, Draft v1.1');
        $this->command->info('   • 5 Levels: No Cover, Level 1-4');
        $this->command->info('   • 4 Feature Groups with 9 features total');
        $this->command->info('   • Feature values configured for all levels');
    }

    private function setFeatureValues($level, $featureValues)
    {
        foreach ($featureValues as $featureId => $data) {
            $level->featureValues()->create([
                'feature_id' => $featureId,
                'value' => $data['value'],
                'is_included' => $data['is_included'] ?? false,
                'display_value' => $data['display_value'] ?? null,
            ]);
        }
    }
}
