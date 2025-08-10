<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\PlanCategory;
use App\Models\ServicePlanNew;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux\Flux;

class PlansCategory extends Component
{
    public Company $company;
    public PlanCategory $category;

    // Modal states
    public $showCreatePlanModal = false;
    public $showEditPlanModal = false;
    public $showDeletePlanModal = false;

    // Forms
    public $createPlanForm = [
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#3B82F6',
    ];

    public $editPlanForm = [
        'id' => null,
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#3B82F6',
    ];

    public $deletePlanForm = [
        'id' => null,
        'name' => '',
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

    // Create Plan
    public function createPlan()
    {
        $this->validate([
            'createPlanForm.name' => 'required|string|max:255',
            'createPlanForm.description' => 'nullable|string',
            'createPlanForm.is_active' => 'boolean',
            'createPlanForm.color' => 'required|string|max:7',
        ]);

        $plan = $this->category->plans()->create(array_merge(
            $this->createPlanForm,
            [
                'company_id' => $this->company->id,
                'sort_order' => $this->category->plans()->count(),
            ]
        ));

        // Automatically create the first revision
        $revision = $plan->revisions()->create([
            'name' => 'Initial Version',
            'description' => 'First revision of ' . $plan->name,
            'status' => 'draft',
            'version_number' => 1,
            'is_current' => true,
        ]);

        $this->showCreatePlanModal = false;
        $this->resetCreatePlanForm();

        Flux::toast('Plan created successfully!', variant: 'success');
    }

    // Edit Plan
    public function editPlan($planId)
    {
        $plan = ServicePlanNew::find($planId);

        if (!$plan || $plan->company_id !== $this->company->id) {
            Flux::toast('Plan not found.', variant: 'danger');
            return;
        }

        $this->editPlanForm = [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description ?? '',
            'is_active' => $plan->is_active,
            'color' => $plan->color ?? '#3B82F6',
        ];

        $this->showEditPlanModal = true;
    }

    public function updatePlan()
    {
        $this->validate([
            'editPlanForm.name' => 'required|string|max:255',
            'editPlanForm.description' => 'nullable|string',
            'editPlanForm.is_active' => 'boolean',
            'editPlanForm.color' => 'required|string|max:7',
        ]);

        $plan = ServicePlanNew::find($this->editPlanForm['id']);

        if (!$plan || $plan->company_id !== $this->company->id) {
            Flux::toast('Plan not found.', variant: 'danger');
            return;
        }

        $plan->update([
            'name' => $this->editPlanForm['name'],
            'description' => $this->editPlanForm['description'],
            'is_active' => $this->editPlanForm['is_active'],
            'color' => $this->editPlanForm['color'],
        ]);

        $this->showEditPlanModal = false;
        $this->resetEditPlanForm();

        Flux::toast('Plan updated successfully!', variant: 'success');
    }

    // Duplicate Plan
    public function duplicatePlan($planId)
    {
        $originalPlan = ServicePlanNew::find($planId);

        if (!$originalPlan || $originalPlan->company_id !== $this->company->id) {
            Flux::toast('Plan not found.', variant: 'danger');
            return;
        }

        $duplicatedPlan = $originalPlan->replicate([
            'created_at',
            'updated_at'
        ]);
        $duplicatedPlan->name = $originalPlan->name . ' (Copy)';
        $duplicatedPlan->sort_order = $this->category->plans()->count();
        $duplicatedPlan->save();

        // Automatically create the first revision for duplicated plan
        $duplicatedPlan->revisions()->create([
            'name' => 'Initial Version',
            'description' => 'First revision of ' . $duplicatedPlan->name,
            'status' => 'draft',
            'version_number' => 1,
            'is_current' => true,
        ]);

        Flux::toast('Plan duplicated successfully!', variant: 'success');
    }

    // Delete Plan
    public function confirmDeletePlan($planId)
    {
        $plan = ServicePlanNew::find($planId);

        if (!$plan || $plan->company_id !== $this->company->id) {
            Flux::toast('Plan not found.', variant: 'danger');
            return;
        }

        $this->deletePlanForm = [
            'id' => $plan->id,
            'name' => $plan->name,
        ];

        $this->showDeletePlanModal = true;
    }

    public function deletePlan()
    {
        $plan = ServicePlanNew::find($this->deletePlanForm['id']);

        if (!$plan || $plan->company_id !== $this->company->id) {
            Flux::toast('Plan not found.', variant: 'danger');
            return;
        }

        $planName = $plan->name;
        $plan->delete();

        $this->showDeletePlanModal = false;
        $this->resetDeletePlanForm();

        Flux::toast("Plan '{$planName}' deleted successfully!", variant: 'success');
    }

    // Modal controls
    public function openCreatePlanModal()
    {
        $this->resetCreatePlanForm();
        $this->showCreatePlanModal = true;
    }

    // Form resets
    public function resetCreatePlanForm()
    {
        $this->createPlanForm = [
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color' => '#3B82F6',
        ];
    }

    public function resetEditPlanForm()
    {
        $this->editPlanForm = [
            'id' => null,
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color' => '#3B82F6',
        ];
    }

    public function resetDeletePlanForm()
    {
        $this->deletePlanForm = [
            'id' => null,
            'name' => '',
        ];
    }

    #[Layout('components.layouts.company')]
    public function render()
    {
        $plans = $this->category->plans()
            ->withCount(['revisions'])
            ->with(['revisions' => function($query) {
                $query->where('is_current', true)->orWhere('status', 'published')->limit(1);
            }])
            ->ordered()
            ->get();

        return view('livewire.company.plans-category', compact('plans'));
    }
}