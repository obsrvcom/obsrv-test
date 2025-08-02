<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\ServicePlanNew;
use App\Models\ServicePlanRevision;
use App\Models\ServicePlanLevel;
use App\Models\ServicePlanFeatureGroupNew;
use App\Models\ServicePlanFeatureNew;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux\Flux;

class ServicePlansNew extends Component
{
    public Company $company;

    // Selected items for navigation
    public $selectedPlan = null;
    public $selectedRevision = null;
    public $selectedLevel = null;

    // Modal states
    public $showCreatePlanModal = false;
    public $showCreateRevisionModal = false;
    public $showCreateLevelModal = false;
    public $showCreateFeatureGroupModal = false;
    public $showCreateFeatureModal = false;
    public $showManageLevelFeaturesModal = false;
    public $showPublishRevisionModal = false;

    // Form data
    public $planForm = [
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#3B82F6',
    ];

    public $revisionForm = [
        'service_plan_id' => null,
        'name' => '',
        'description' => '',
        'status' => 'draft',
    ];

    public $levelForm = [
        'service_plan_revision_id' => null,
        'name' => '',
        'description' => '',
        'monthly_price' => null,
        'quarterly_price' => null,
        'annual_price' => null,
        'minimum_contract_months' => null,
        'is_active' => true,
        'is_featured' => false,
        'color' => '#3B82F6',
    ];

    public $featureGroupForm = [
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#3B82F6',
    ];

    public $featureForm = [
        'feature_group_id' => null,
        'name' => '',
        'description' => '',
        'data_type' => 'boolean',
        'options' => [],
        'is_active' => true,
        'affects_sla' => false,
        'unit' => '',
    ];

    // For managing level features
    public $selectedLevelForFeatures = null;
    public $availableFeatureGroups = [];
    public $levelFeatureGroups = [];

    public function mount()
    {
        $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

        // Set the first plan as selected if available
        $firstPlan = $this->company->servicePlansNew()->active()->ordered()->first();
        if ($firstPlan) {
            $this->selectedPlan = $firstPlan->id;

            // Set the current revision as selected
            $currentRevision = $firstPlan->getCurrentRevision();
            if ($currentRevision) {
                $this->selectedRevision = $currentRevision->id;
            }
        }
    }

    // Plan Management
    public function createPlan()
    {
        $this->validate([
            'planForm.name' => 'required|string|max:255',
            'planForm.description' => 'nullable|string',
            'planForm.is_active' => 'boolean',
        ]);

        $plan = $this->company->servicePlansNew()->create(array_merge(
            $this->planForm,
            ['sort_order' => $this->company->servicePlansNew()->count()]
        ));

        // Create initial draft revision
        $revision = $plan->revisions()->create([
            'name' => 'v1.0 Draft',
            'description' => 'Initial draft revision',
            'status' => 'draft',
            'version_number' => 1,
            'is_current' => true,
        ]);

        $this->showCreatePlanModal = false;
        $this->resetPlanForm();

        Flux::toast('Service plan created successfully!', variant: 'success');

        // Navigate to edit the new plan
        return $this->editPlan($plan->id, $revision->id);
    }

    // Revision Management
    public function createRevision()
    {
        $this->validate([
            'revisionForm.service_plan_id' => 'required|exists:service_plans_new,id',
            'revisionForm.name' => 'required|string|max:255',
            'revisionForm.description' => 'nullable|string',
        ]);

        $plan = ServicePlanNew::find($this->revisionForm['service_plan_id']);
        $nextVersion = $plan->revisions()->max('version_number') + 1;

        $revision = $plan->revisions()->create(array_merge(
            $this->revisionForm,
            [
                'version_number' => $nextVersion,
                'is_current' => false, // New revisions start as non-current
            ]
        ));

        $this->selectedRevision = $revision->id;
        $this->showCreateRevisionModal = false;
        $this->resetRevisionForm();

        Flux::toast('Revision created successfully!', variant: 'success');
    }

    // Level Management
    public function createLevel()
    {
        $this->validate([
            'levelForm.service_plan_revision_id' => 'required|exists:service_plan_revisions,id',
            'levelForm.name' => 'required|string|max:255',
            'levelForm.description' => 'nullable|string',
            'levelForm.monthly_price' => 'nullable|numeric|min:0',
            'levelForm.quarterly_price' => 'nullable|numeric|min:0',
            'levelForm.annual_price' => 'nullable|numeric|min:0',
            'levelForm.minimum_contract_months' => 'nullable|integer|min:1',
        ]);

        $revision = ServicePlanRevision::find($this->levelForm['service_plan_revision_id']);
        $level = $revision->levels()->create(array_merge(
            $this->levelForm,
            ['sort_order' => $revision->levels()->count()]
        ));

        $this->selectedLevel = $level->id;
        $this->showCreateLevelModal = false;
        $this->resetLevelForm();

        Flux::toast('Level created successfully!', variant: 'success');
    }

    // Feature Group Management
    public function createFeatureGroup()
    {
        $this->validate([
            'featureGroupForm.name' => 'required|string|max:255',
            'featureGroupForm.description' => 'nullable|string',
            'featureGroupForm.is_active' => 'boolean',
        ]);

        $featureGroup = $this->company->servicePlanFeatureGroupsNew()->create(array_merge(
            $this->featureGroupForm,
            ['sort_order' => $this->company->servicePlanFeatureGroupsNew()->count()]
        ));

        $this->showCreateFeatureGroupModal = false;
        $this->resetFeatureGroupForm();

        Flux::toast('Feature group created successfully!', variant: 'success');
    }

    // Feature Management
    public function createFeature()
    {
        $this->validate([
            'featureForm.feature_group_id' => 'required|exists:service_plan_feature_groups_new,id',
            'featureForm.name' => 'required|string|max:255',
            'featureForm.description' => 'nullable|string',
            'featureForm.data_type' => 'required|in:boolean,text,number,currency,time,select',
            'featureForm.is_active' => 'boolean',
            'featureForm.affects_sla' => 'boolean',
        ]);

        $featureGroup = ServicePlanFeatureGroupNew::find($this->featureForm['feature_group_id']);
        $feature = $featureGroup->features()->create(array_merge(
            $this->featureForm,
            ['sort_order' => $featureGroup->features()->count()]
        ));

        $this->showCreateFeatureModal = false;
        $this->resetFeatureForm();

        Flux::toast('Feature created successfully!', variant: 'success');
    }

    // Publishing
    public function publishRevision($revisionId)
    {
        $revision = ServicePlanRevision::find($revisionId);
        $revision->publish();

        $this->selectedRevision = $revisionId;
        Flux::toast('Revision published successfully!', variant: 'success');
    }

    // Modal Management
    public function openCreatePlanModal()
    {
        $this->resetPlanForm();
        $this->showCreatePlanModal = true;
    }

    public function openCreateRevisionModal($planId = null)
    {
        $this->resetRevisionForm();
        if ($planId) {
            $this->revisionForm['service_plan_id'] = $planId;
        }
        $this->showCreateRevisionModal = true;
    }

    public function openCreateLevelModal($revisionId = null)
    {
        $this->resetLevelForm();
        if ($revisionId) {
            $this->levelForm['service_plan_revision_id'] = $revisionId;
        }
        $this->showCreateLevelModal = true;
    }

    public function openCreateFeatureGroupModal()
    {
        $this->resetFeatureGroupForm();
        $this->showCreateFeatureGroupModal = true;
    }

    public function openCreateFeatureModal($featureGroupId = null)
    {
        $this->resetFeatureForm();
        if ($featureGroupId) {
            $this->featureForm['feature_group_id'] = $featureGroupId;
        }
        $this->showCreateFeatureModal = true;
    }

    // Form Reset Methods
    private function resetPlanForm()
    {
        $this->planForm = [
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color' => '#3B82F6',
        ];
    }

    private function resetRevisionForm()
    {
        $this->revisionForm = [
            'service_plan_id' => null,
            'name' => '',
            'description' => '',
            'status' => 'draft',
        ];
    }

    private function resetLevelForm()
    {
        $this->levelForm = [
            'service_plan_revision_id' => null,
            'name' => '',
            'description' => '',
            'monthly_price' => null,
            'quarterly_price' => null,
            'annual_price' => null,
            'minimum_contract_months' => null,
            'is_active' => true,
            'is_featured' => false,
            'color' => '#3B82F6',
        ];
    }

    private function resetFeatureGroupForm()
    {
        $this->featureGroupForm = [
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color' => '#3B82F6',
        ];
    }

    private function resetFeatureForm()
    {
        $this->featureForm = [
            'feature_group_id' => null,
            'name' => '',
            'description' => '',
            'data_type' => 'boolean',
            'options' => [],
            'is_active' => true,
            'affects_sla' => false,
            'unit' => '',
        ];
    }

    // Selection Methods
    public function selectPlan($planId)
    {
        $this->selectedPlan = $planId;
        $this->selectedRevision = null;
        $this->selectedLevel = null;

        // Auto-select current revision
        $plan = ServicePlanNew::find($planId);
        if ($plan) {
            $currentRevision = $plan->getCurrentRevision();
            if ($currentRevision) {
                $this->selectedRevision = $currentRevision->id;
            }
        }
    }

    public function selectRevision($revisionId)
    {
        $this->selectedRevision = $revisionId;
        $this->selectedLevel = null;
    }

    public function selectLevel($levelId)
    {
        $this->selectedLevel = $levelId;
    }

    // Navigation methods
    public function editPlan($planId, $revisionId = null)
    {
        $plan = ServicePlanNew::find($planId);

        if ($revisionId) {
            $revision = ServicePlanRevision::find($revisionId);
            return redirect()->route('app.company.service.plans.edit.revision', [
                'company' => $this->company,
                'plan' => $plan,
                'revision' => $revision
            ]);
        } else {
            return redirect()->route('app.company.service.plans.edit', [
                'company' => $this->company,
                'plan' => $plan
            ]);
        }
    }

    public function editRevision($revisionId)
    {
        $revision = ServicePlanRevision::find($revisionId);

        if (!$revision) {
            Flux::toast('Revision not found.', variant: 'danger');
            return;
        }

        return redirect()->route('app.company.service.plans.edit.revision', [
            'company' => $this->company,
            'plan' => $revision->servicePlan,
            'revision' => $revision
        ]);
    }

    public function duplicatePlan($planId)
    {
        Flux::toast('Duplicate plan functionality coming soon.', variant: 'info');
    }

    public function archivePlan($planId)
    {
        Flux::toast('Archive plan functionality coming soon.', variant: 'info');
    }

    public function archiveRevision($revisionId)
    {
        Flux::toast('Archive revision functionality coming soon.', variant: 'info');
    }

    public function editLevel($levelId)
    {
        Flux::toast('Edit level functionality coming soon.', variant: 'info');
    }

    public function manageLevelFeatures($levelId)
    {
        Flux::toast('Manage level features functionality coming soon.', variant: 'info');
    }

    public function editFeatureGroup($featureGroupId)
    {
        Flux::toast('Edit feature group functionality coming soon.', variant: 'info');
    }

    public function editFeature($featureId)
    {
        Flux::toast('Edit feature functionality coming soon.', variant: 'info');
    }

    // Additional placeholder methods
    public function deactivateFeatureGroup($featureGroupId)
    {
        Flux::toast('Deactivate feature group functionality coming soon.', variant: 'info');
    }

    public function activateFeatureGroup($featureGroupId)
    {
        Flux::toast('Activate feature group functionality coming soon.', variant: 'info');
    }

    public function deactivateFeature($featureId)
    {
        Flux::toast('Deactivate feature functionality coming soon.', variant: 'info');
    }

    public function activateFeature($featureId)
    {
        Flux::toast('Activate feature functionality coming soon.', variant: 'info');
    }

    public function manageFeatureOptions($featureId)
    {
        Flux::toast('Manage feature options functionality coming soon.', variant: 'info');
    }

    public function deleteLevel($levelId)
    {
        Flux::toast('Delete level functionality coming soon.', variant: 'info');
    }

    public function editFeatureValue($levelId, $featureId)
    {
        Flux::toast('Edit feature value functionality coming soon.', variant: 'info');
    }

    #[Layout('components.layouts.company')]
    public function render()
    {
        $servicePlans = $this->company->servicePlansNew()
            ->with(['revisions' => function($query) {
                $query->orderBy('version_number', 'desc');
            }])
            ->active()
            ->ordered()
            ->get();

        $featureGroups = $this->company->servicePlanFeatureGroupsNew()
            ->with(['features' => function($query) {
                $query->active()->ordered();
            }])
            ->active()
            ->ordered()
            ->get();

        return view('livewire.company.service-plans-new', compact(
            'servicePlans',
            'featureGroups'
        ));
    }
}
