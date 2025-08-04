<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\ServicePlanFeatureGroupNew;
use App\Models\ServicePlanFeatureNew;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux\Flux;

class ServicePlanFeatures extends Component
{
    public Company $company;

    // Modal states
    public $showCreateFeatureGroupModal = false;
    public $showEditFeatureGroupModal = false;
    public $showCreateFeatureModal = false;
    public $showEditFeatureModal = false;
    public $editingFeatureGroup = null;
    public $editingFeature = null;

    // Form data
    public $featureGroupForm = [
        'name' => '',
        'description' => '',
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

    #[Layout('components.layouts.company')]
    public function render()
    {
        $featureGroups = $this->company->servicePlanFeatureGroupsNew()
            ->with('features')
            ->ordered()
            ->get();

        return view('livewire.company.service-plan-features', [
            'featureGroups' => $featureGroups,
        ]);
    }

    public function mount()
    {
        $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
    }

    // Feature Group Management
    public function openCreateFeatureGroupModal()
    {
        $this->resetFeatureGroupForm();
        $this->showCreateFeatureGroupModal = true;
    }

    public function createFeatureGroup()
    {
        $this->validate([
            'featureGroupForm.name' => 'required|string|max:255',
            'featureGroupForm.description' => 'nullable|string',
        ]);

        $featureGroup = $this->company->servicePlanFeatureGroupsNew()->create(array_merge(
            $this->featureGroupForm,
            ['sort_order' => $this->company->servicePlanFeatureGroupsNew()->count()]
        ));

        $this->showCreateFeatureGroupModal = false;
        $this->resetFeatureGroupForm();

        Flux::toast('Feature group created successfully.', variant: 'success');
    }

    public function editFeatureGroup($featureGroupId)
    {
        $this->editingFeatureGroup = ServicePlanFeatureGroupNew::findOrFail($featureGroupId);
        $this->featureGroupForm = [
            'name' => $this->editingFeatureGroup->name,
            'description' => $this->editingFeatureGroup->description,
            'color' => $this->editingFeatureGroup->color,
        ];
        $this->showEditFeatureGroupModal = true;
    }

    public function updateFeatureGroup()
    {
        $this->validate([
            'featureGroupForm.name' => 'required|string|max:255',
            'featureGroupForm.description' => 'nullable|string',
        ]);

        $this->editingFeatureGroup->update($this->featureGroupForm);

        $this->showEditFeatureGroupModal = false;
        $this->resetFeatureGroupForm();
        $this->editingFeatureGroup = null;

        Flux::toast('Feature group updated successfully.', variant: 'success');
    }

    // Feature Management
    public function openCreateFeatureModal($featureGroupId = null)
    {
        $this->resetFeatureForm();
        if ($featureGroupId) {
            $this->featureForm['feature_group_id'] = $featureGroupId;
        }
        $this->showCreateFeatureModal = true;
    }

    public function createFeature()
    {
        $this->validate([
            'featureForm.feature_group_id' => 'required|exists:service_plan_feature_groups_new,id',
            'featureForm.name' => 'required|string|max:255',
            'featureForm.description' => 'nullable|string',
            'featureForm.data_type' => 'required|in:boolean,integer,string,decimal,select',
            'featureForm.options' => 'array',
            'featureForm.is_active' => 'boolean',
            'featureForm.affects_sla' => 'boolean',
            'featureForm.unit' => 'nullable|string|max:50',
        ]);

        $feature = ServicePlanFeatureNew::create(array_merge(
            $this->featureForm,
            ['sort_order' => ServicePlanFeatureNew::where('feature_group_id', $this->featureForm['feature_group_id'])->count()]
        ));

        $this->showCreateFeatureModal = false;
        $this->resetFeatureForm();

        Flux::toast('Feature created successfully.', variant: 'success');
    }

    public function editFeature($featureId)
    {
        $this->editingFeature = ServicePlanFeatureNew::findOrFail($featureId);
        $this->featureForm = [
            'feature_group_id' => $this->editingFeature->feature_group_id,
            'name' => $this->editingFeature->name,
            'description' => $this->editingFeature->description,
            'data_type' => $this->editingFeature->data_type,
            'options' => $this->editingFeature->options ?? [],
            'is_active' => $this->editingFeature->is_active,
            'affects_sla' => $this->editingFeature->affects_sla,
            'unit' => $this->editingFeature->unit,
        ];
        $this->showEditFeatureModal = true;
    }

    public function updateFeature()
    {
        $this->validate([
            'featureForm.name' => 'required|string|max:255',
            'featureForm.description' => 'nullable|string',
            'featureForm.data_type' => 'required|in:boolean,integer,string,decimal,select',
            'featureForm.options' => 'array',
            'featureForm.is_active' => 'boolean',
            'featureForm.affects_sla' => 'boolean',
            'featureForm.unit' => 'nullable|string|max:50',
        ]);

        $this->editingFeature->update($this->featureForm);

        $this->showEditFeatureModal = false;
        $this->resetFeatureForm();
        $this->editingFeature = null;

        Flux::toast('Feature updated successfully.', variant: 'success');
    }

    public function deactivateFeature($featureId)
    {
        $feature = ServicePlanFeatureNew::findOrFail($featureId);
        $feature->update(['is_active' => false]);

        Flux::toast('Feature deactivated successfully.', variant: 'success');
    }

    public function activateFeature($featureId)
    {
        $feature = ServicePlanFeatureNew::findOrFail($featureId);
        $feature->update(['is_active' => true]);

        Flux::toast('Feature activated successfully.', variant: 'success');
    }

    public function manageFeatureOptions($featureId)
    {
        Flux::toast('Manage feature options functionality coming soon.', variant: 'info');
    }

    // Helper methods
    private function resetFeatureGroupForm()
    {
        $this->featureGroupForm = [
            'name' => '',
            'description' => '',
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
}
