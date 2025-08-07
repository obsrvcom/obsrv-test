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

    // Forms
    public $createPlanForm = [
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#3B82F6',
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

        $this->showCreatePlanModal = false;
        $this->resetCreatePlanForm();

        Flux::toast('Plan created successfully!', variant: 'success');

        // Redirect to plan edit page
        return redirect()->route('company.plans.edit', [
            'company' => $this->company,
            'plan' => $plan
        ]);
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