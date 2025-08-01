<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\ServicePlanGroup;
use App\Models\ServicePlan;
use App\Models\ServicePlanFeatureCategory;
use App\Models\ServicePlanFeature;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ServicePlans extends Component
{
    public Company $company;

    // Component state
    public $activeTab = 'overview';
    public $selectedGroup = null;

    // Create/Edit state
    public $showCreateGroupModal = false;
    public $showCreatePlanModal = false;
    public $showCreateCategoryModal = false;
    public $showCreateFeatureModal = false;

    // Form data
    public $groupForm = [
        'name' => '',
        'description' => '',
        'is_active' => true,
    ];

    public $planForm = [
        'service_plan_group_id' => null,
        'name' => '',
        'description' => '',
        'is_active' => true,
        'is_featured' => false,
        'color' => '#3B82F6',
        'base_price_monthly' => null,
        'base_price_quarterly' => null,
        'base_price_annually' => null,
        'minimum_contract_months' => null,
    ];

    public $categoryForm = [
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#3B82F6',
    ];

    public $featureForm = [
        'service_plan_feature_category_id' => null,
        'name' => '',
        'description' => '',
        'data_type' => 'boolean',
        'options' => [],
        'is_active' => true,
        'affects_sla' => false,
        'unit' => '',
    ];

    public function mount()
    {
        $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

        // Set the first group as selected if available
        $firstGroup = $this->company->servicePlanGroups()->active()->ordered()->first();
        if ($firstGroup) {
            $this->selectedGroup = $firstGroup->id;
        }
    }

    public function createGroup()
    {
        $this->validate([
            'groupForm.name' => 'required|string|max:255',
            'groupForm.description' => 'nullable|string',
            'groupForm.is_active' => 'boolean',
        ]);

        $group = $this->company->servicePlanGroups()->create(array_merge(
            $this->groupForm,
            ['sort_order' => $this->company->servicePlanGroups()->count()]
        ));

        $this->selectedGroup = $group->id;
        $this->showCreateGroupModal = false;
        $this->resetGroupForm();

        $this->toast('Service plan group created successfully!', variant: 'success');
    }

    public function createPlan()
    {
        $this->validate([
            'planForm.service_plan_group_id' => 'required|exists:service_plan_groups,id',
            'planForm.name' => 'required|string|max:255',
            'planForm.description' => 'nullable|string',
            'planForm.base_price_monthly' => 'nullable|numeric|min:0',
            'planForm.base_price_quarterly' => 'nullable|numeric|min:0',
            'planForm.base_price_annually' => 'nullable|numeric|min:0',
            'planForm.minimum_contract_months' => 'nullable|integer|min:1',
        ]);

        $group = ServicePlanGroup::findOrFail($this->planForm['service_plan_group_id']);
        $plan = $group->servicePlans()->create(array_merge(
            $this->planForm,
            ['sort_order' => $group->servicePlans()->count()]
        ));

        $this->showCreatePlanModal = false;
        $this->resetPlanForm();

        $this->toast('Service plan created successfully!', variant: 'success');
    }

    public function createCategory()
    {
        $this->validate([
            'categoryForm.name' => 'required|string|max:255',
            'categoryForm.description' => 'nullable|string',
            'categoryForm.is_active' => 'boolean',
        ]);

        $category = $this->company->servicePlanFeatureCategories()->create(array_merge(
            $this->categoryForm,
            ['sort_order' => $this->company->servicePlanFeatureCategories()->count()]
        ));

        $this->showCreateCategoryModal = false;
        $this->resetCategoryForm();

        $this->toast('Feature category created successfully!', variant: 'success');
    }

    public function createFeature()
    {
        $this->validate([
            'featureForm.service_plan_feature_category_id' => 'required|exists:service_plan_feature_categories,id',
            'featureForm.name' => 'required|string|max:255',
            'featureForm.description' => 'nullable|string',
            'featureForm.data_type' => 'required|in:boolean,text,number,currency,time,select',
            'featureForm.unit' => 'nullable|string|max:50',
        ]);

        $category = ServicePlanFeatureCategory::findOrFail($this->featureForm['service_plan_feature_category_id']);
        $feature = $category->features()->create(array_merge(
            $this->featureForm,
            ['sort_order' => $category->features()->count()]
        ));

        $this->showCreateFeatureModal = false;
        $this->resetFeatureForm();

        $this->toast('Feature created successfully!', variant: 'success');
    }

    public function selectGroup($groupId)
    {
        $this->selectedGroup = $groupId;
    }

    public function openCreateGroupModal()
    {
        $this->resetGroupForm();
        $this->showCreateGroupModal = true;
    }

    public function openCreatePlanModal($groupId = null)
    {
        $this->resetPlanForm();
        if ($groupId) {
            $this->planForm['service_plan_group_id'] = $groupId;
        }
        $this->showCreatePlanModal = true;
    }

    public function openCreateCategoryModal()
    {
        $this->resetCategoryForm();
        $this->showCreateCategoryModal = true;
    }

    public function openCreateFeatureModal($categoryId = null)
    {
        $this->resetFeatureForm();
        if ($categoryId) {
            $this->featureForm['service_plan_feature_category_id'] = $categoryId;
        }
        $this->showCreateFeatureModal = true;
    }

    private function resetGroupForm()
    {
        $this->groupForm = [
            'name' => '',
            'description' => '',
            'is_active' => true,
        ];
    }

    private function resetPlanForm()
    {
        $this->planForm = [
            'service_plan_group_id' => null,
            'name' => '',
            'description' => '',
            'is_active' => true,
            'is_featured' => false,
            'color' => '#3B82F6',
            'base_price_monthly' => null,
            'base_price_quarterly' => null,
            'base_price_annually' => null,
            'minimum_contract_months' => null,
        ];
    }

    private function resetCategoryForm()
    {
        $this->categoryForm = [
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color' => '#3B82F6',
        ];
    }

    private function resetFeatureForm()
    {
        $this->featureForm = [
            'service_plan_feature_category_id' => null,
            'name' => '',
            'description' => '',
            'data_type' => 'boolean',
            'options' => [],
            'is_active' => true,
            'affects_sla' => false,
            'unit' => '',
        ];
    }

    // Placeholder methods for dropdown actions
    public function editGroup($groupId)
    {
        $this->toast('Edit group functionality coming soon.', variant: 'info');
    }

    public function duplicateGroup($groupId)
    {
        $this->toast('Duplicate group functionality coming soon.', variant: 'info');
    }

    public function deactivateGroup($groupId)
    {
        $this->toast('Deactivate group functionality coming soon.', variant: 'info');
    }

    public function activateGroup($groupId)
    {
        $this->toast('Activate group functionality coming soon.', variant: 'info');
    }

    public function confirmDeleteGroup($groupId)
    {
        $this->toast('Delete group functionality coming soon.', variant: 'info');
    }

    public function editPlan($planId)
    {
        $this->toast('Edit plan functionality coming soon.', variant: 'info');
    }

    public function managePlanFeatures($planId)
    {
        $this->toast('Manage plan features functionality coming soon.', variant: 'info');
    }

    public function confirmDeletePlan($planId)
    {
        $this->toast('Delete plan functionality coming soon.', variant: 'info');
    }

    public function editCategory($categoryId)
    {
        $this->toast('Edit category functionality coming soon.', variant: 'info');
    }

    public function deactivateCategory($categoryId)
    {
        $this->toast('Deactivate category functionality coming soon.', variant: 'info');
    }

    public function activateCategory($categoryId)
    {
        $this->toast('Activate category functionality coming soon.', variant: 'info');
    }

    public function confirmDeleteCategory($categoryId)
    {
        $this->toast('Delete category functionality coming soon.', variant: 'info');
    }

    public function editFeature($featureId)
    {
        $this->toast('Edit feature functionality coming soon.', variant: 'info');
    }

    public function manageFeatureOptions($featureId)
    {
        $this->toast('Manage feature options functionality coming soon.', variant: 'info');
    }

    public function deactivateFeature($featureId)
    {
        $this->toast('Deactivate feature functionality coming soon.', variant: 'info');
    }

    public function activateFeature($featureId)
    {
        $this->toast('Activate feature functionality coming soon.', variant: 'info');
    }

    public function confirmDeleteFeature($featureId)
    {
        $this->toast('Delete feature functionality coming soon.', variant: 'info');
    }

    #[Layout('components.layouts.company')]
    public function render()
    {
        $servicePlanGroups = $this->company->servicePlanGroups()
            ->with(['servicePlans' => function($query) {
                $query->active()->ordered();
            }])
            ->active()
            ->ordered()
            ->get();

        $featureCategories = $this->company->servicePlanFeatureCategories()
            ->with(['features' => function($query) {
                $query->active()->ordered();
            }])
            ->active()
            ->ordered()
            ->get();

        $selectedGroupData = null;
        if ($this->selectedGroup) {
            $selectedGroupData = ServicePlanGroup::with([
                'servicePlans' => function($query) {
                    $query->active()->ordered()->with(['featureValues.feature.featureCategory']);
                }
            ])->find($this->selectedGroup);
        }

        return view('livewire.company.service-plans', [
            'servicePlanGroups' => $servicePlanGroups,
            'featureCategories' => $featureCategories,
            'selectedGroupData' => $selectedGroupData,
        ]);
    }
}
