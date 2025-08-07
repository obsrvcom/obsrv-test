<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\PlanCategory;
use App\Models\ServicePlanFeatureGroupNew;
use App\Models\ServicePlanFeatureNew;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux\Flux;

class CategoryFeatures extends Component
{
    public Company $company;
    public PlanCategory $category;

    // Modal states
    public $showCreateGroupModal = false;
    public $showCreateFeatureModal = false;
    public $showEditGroupModal = false;
    public $showEditFeatureModal = false;

    // Forms
    public $createGroupForm = [
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#6366F1',
    ];

    public $createFeatureForm = [
        'feature_group_id' => '',
        'name' => '',
        'description' => '',
        'data_type' => 'boolean',
        'is_active' => true,
        'affects_sla' => false,
        'unit' => '',
        'options' => [],
    ];

    public $editGroupForm = [
        'id' => null,
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#6366F1',
    ];

    public $editFeatureForm = [
        'id' => null,
        'name' => '',
        'description' => '',
        'data_type' => 'boolean',
        'is_active' => true,
        'affects_sla' => false,
        'unit' => '',
        'options' => [],
    ];

    public function mount(PlanCategory $category)
    {
        $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
        
        // Ensure the category belongs to the current company
        if ($category->company_id !== $this->company->id) {
            abort(404);
        }

        $this->category = $category;
    }

    // Create Feature Group
    public function createFeatureGroup()
    {
        $this->validate([
            'createGroupForm.name' => 'required|string|max:255',
            'createGroupForm.description' => 'nullable|string',
            'createGroupForm.is_active' => 'boolean',
            'createGroupForm.color' => 'required|string|max:7',
        ]);

        $featureGroup = $this->category->featureGroups()->create(array_merge(
            $this->createGroupForm,
            [
                'company_id' => $this->company->id,
                'sort_order' => $this->category->featureGroups()->count(),
            ]
        ));

        $this->showCreateGroupModal = false;
        $this->resetCreateGroupForm();

        Flux::toast('Feature group created successfully!', variant: 'success');
    }

    // Create Feature
    public function createFeature()
    {
        $this->validate([
            'createFeatureForm.feature_group_id' => 'required|exists:service_plan_feature_groups_new,id',
            'createFeatureForm.name' => 'required|string|max:255',
            'createFeatureForm.description' => 'nullable|string',
            'createFeatureForm.data_type' => 'required|in:boolean,text,number,currency,time,select',
            'createFeatureForm.is_active' => 'boolean',
            'createFeatureForm.affects_sla' => 'boolean',
            'createFeatureForm.unit' => 'nullable|string|max:50',
        ]);

        $featureGroup = ServicePlanFeatureGroupNew::find($this->createFeatureForm['feature_group_id']);
        
        // Ensure feature group belongs to this category
        if ($featureGroup->category_id !== $this->category->id) {
            Flux::toast('Invalid feature group selected.', variant: 'danger');
            return;
        }

        $feature = $featureGroup->features()->create(array_merge(
            $this->createFeatureForm,
            ['sort_order' => $featureGroup->features()->count()]
        ));

        $this->showCreateFeatureModal = false;
        $this->resetCreateFeatureForm();

        Flux::toast('Feature created successfully!', variant: 'success');
    }

    // Edit Feature Group
    public function editFeatureGroup($groupId)
    {
        $group = ServicePlanFeatureGroupNew::find($groupId);

        if (!$group || $group->category_id !== $this->category->id) {
            Flux::toast('Feature group not found.', variant: 'danger');
            return;
        }

        $this->editGroupForm = [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description ?? '',
            'is_active' => $group->is_active,
            'color' => $group->color,
        ];

        $this->showEditGroupModal = true;
    }

    public function updateFeatureGroup()
    {
        $this->validate([
            'editGroupForm.name' => 'required|string|max:255',
            'editGroupForm.description' => 'nullable|string',
            'editGroupForm.is_active' => 'boolean',
            'editGroupForm.color' => 'required|string|max:7',
        ]);

        $group = ServicePlanFeatureGroupNew::find($this->editGroupForm['id']);

        if (!$group || $group->category_id !== $this->category->id) {
            Flux::toast('Feature group not found.', variant: 'danger');
            return;
        }

        $group->update([
            'name' => $this->editGroupForm['name'],
            'description' => $this->editGroupForm['description'],
            'is_active' => $this->editGroupForm['is_active'],
            'color' => $this->editGroupForm['color'],
        ]);

        $this->showEditGroupModal = false;
        $this->resetEditGroupForm();

        Flux::toast('Feature group updated successfully!', variant: 'success');
    }

    // Edit Feature
    public function editFeature($featureId)
    {
        $feature = ServicePlanFeatureNew::find($featureId);

        if (!$feature || $feature->featureGroup->category_id !== $this->category->id) {
            Flux::toast('Feature not found.', variant: 'danger');
            return;
        }

        $this->editFeatureForm = [
            'id' => $feature->id,
            'name' => $feature->name,
            'description' => $feature->description ?? '',
            'data_type' => $feature->data_type,
            'is_active' => $feature->is_active,
            'affects_sla' => $feature->affects_sla,
            'unit' => $feature->unit ?? '',
            'options' => $feature->options ?? [],
        ];

        $this->showEditFeatureModal = true;
    }

    public function updateFeature()
    {
        $this->validate([
            'editFeatureForm.name' => 'required|string|max:255',
            'editFeatureForm.description' => 'nullable|string',
            'editFeatureForm.data_type' => 'required|in:boolean,text,number,currency,time,select',
            'editFeatureForm.is_active' => 'boolean',
            'editFeatureForm.affects_sla' => 'boolean',
            'editFeatureForm.unit' => 'nullable|string|max:50',
        ]);

        $feature = ServicePlanFeatureNew::find($this->editFeatureForm['id']);

        if (!$feature || $feature->featureGroup->category_id !== $this->category->id) {
            Flux::toast('Feature not found.', variant: 'danger');
            return;
        }

        $feature->update([
            'name' => $this->editFeatureForm['name'],
            'description' => $this->editFeatureForm['description'],
            'data_type' => $this->editFeatureForm['data_type'],
            'is_active' => $this->editFeatureForm['is_active'],
            'affects_sla' => $this->editFeatureForm['affects_sla'],
            'unit' => $this->editFeatureForm['unit'],
            'options' => $this->editFeatureForm['options'],
        ]);

        $this->showEditFeatureModal = false;
        $this->resetEditFeatureForm();

        Flux::toast('Feature updated successfully!', variant: 'success');
    }

    // Modal controls
    public function openCreateGroupModal()
    {
        $this->resetCreateGroupForm();
        $this->showCreateGroupModal = true;
    }

    public function openCreateFeatureModal($groupId = null)
    {
        $this->resetCreateFeatureForm();
        if ($groupId) {
            $this->createFeatureForm['feature_group_id'] = $groupId;
        }
        $this->showCreateFeatureModal = true;
    }

    // Form resets
    public function resetCreateGroupForm()
    {
        $this->createGroupForm = [
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color' => '#6366F1',
        ];
    }

    public function resetCreateFeatureForm()
    {
        $this->createFeatureForm = [
            'feature_group_id' => '',
            'name' => '',
            'description' => '',
            'data_type' => 'boolean',
            'is_active' => true,
            'affects_sla' => false,
            'unit' => '',
            'options' => [],
        ];
    }

    public function resetEditGroupForm()
    {
        $this->editGroupForm = [
            'id' => null,
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color' => '#6366F1',
        ];
    }

    public function resetEditFeatureForm()
    {
        $this->editFeatureForm = [
            'id' => null,
            'name' => '',
            'description' => '',
            'data_type' => 'boolean',
            'is_active' => true,
            'affects_sla' => false,
            'unit' => '',
            'options' => [],
        ];
    }

    #[Layout('components.layouts.company')]
    public function render()
    {
        $featureGroups = $this->category->featureGroups()
            ->withCount(['features'])
            ->with(['features' => function($query) {
                $query->active()->ordered();
            }])
            ->active()
            ->ordered()
            ->get();

        return view('livewire.company.category-features', compact('featureGroups'));
    }
}