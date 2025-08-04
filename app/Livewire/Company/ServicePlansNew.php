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

    // For inline editing
    public $editingRevisionData = null;

    // For deletion confirmation
    public $revisionToDelete = null;
    public $planToDelete = null;



    // Modal states
    public $showCreatePlanModal = false;
    public $showEditPlanModal = false;
    public $showDeletePlanModal = false;
    public $showCreateRevisionModal = false;
    public $showEditRevisionModal = false;
    public $showDeleteRevisionModal = false;
    public $showCreateLevelModal = false;

    public $showManageLevelFeaturesModal = false;
    public $showPublishRevisionModal = false;

    // Form data
    public $planForm = [
        'name' => '',
        'description' => '',
    ];

    public $revisionForm = [
        'service_plan_id' => null,
        'name' => '',
        'status' => 'draft',
    ];

    public $editRevisionForm = [
        'id' => null,
        'name' => '',
        'status' => 'draft',
    ];

    public $editPlanForm = [
        'id' => null,
        'name' => '',
        'description' => '',
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



    // For managing level features
    public $selectedLevelForFeatures = null;
    public $availableFeatureGroups = [];
    public $levelFeatureGroups = [];

    public function mount()
    {
        $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

        // Set the first plan as selected if available
        $firstPlan = $this->company->servicePlansNew()->ordered()->first();
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
        ]);

        $plan = $this->company->servicePlansNew()->create(array_merge(
            $this->planForm,
            ['sort_order' => $this->company->servicePlansNew()->count()]
        ));

        // Create initial draft revision
        $revision = $plan->revisions()->create([
            'name' => 'v1.0 Draft',
            'status' => 'draft',
            'version_number' => 1,
            'is_current' => true,
        ]);

        $this->showCreatePlanModal = false;
        $this->resetPlanForm();

        Flux::toast('Service plan created successfully!', variant: 'success');
    }

    // Revision Management
    public function createRevision()
    {
        $this->validate([
            'revisionForm.service_plan_id' => 'required|exists:service_plans_new,id',
            'revisionForm.name' => 'required|string|max:255',
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



    // Publishing
    public function publishRevision($revisionId)
    {
        $revision = ServicePlanRevision::find($revisionId);
        $revision->publish();

        $this->selectedRevision = $revisionId;
        Flux::toast('Revision published successfully!', variant: 'success');
    }

    // Edit Revision
    public function updateRevision()
    {
        $this->validate([
            'editRevisionForm.name' => 'required|string|max:255',
            'editRevisionForm.status' => 'required|in:draft,published,archived',
        ]);

        $revision = ServicePlanRevision::find($this->editRevisionForm['id']);

        if (!$revision) {
            Flux::toast('Revision not found.', variant: 'danger');
            return;
        }

        $revision->update([
            'name' => $this->editRevisionForm['name'],
            'status' => $this->editRevisionForm['status'],
        ]);

        $this->showEditRevisionModal = false;
        $this->resetEditRevisionForm();
        $this->editingRevisionData = null;

        Flux::toast('Revision updated successfully!', variant: 'success');
    }

    // Edit Plan
    public function updatePlan()
    {
        $this->validate([
            'editPlanForm.name' => 'required|string|max:255',
            'editPlanForm.description' => 'nullable|string',
        ]);

        $plan = ServicePlanNew::find($this->editPlanForm['id']);

        if (!$plan || $plan->company_id !== $this->company->id) {
            Flux::toast('Plan not found.', variant: 'danger');
            return;
        }

        $plan->update([
            'name' => $this->editPlanForm['name'],
            'description' => $this->editPlanForm['description'],
        ]);

        $this->showEditPlanModal = false;
        $this->resetEditPlanForm();

        Flux::toast('Plan updated successfully!', variant: 'success');
    }

    // Delete Plan - Show confirmation modal
    public function confirmDeletePlan($planId)
    {
        $plan = ServicePlanNew::find($planId);

        if (!$plan || $plan->company_id !== $this->company->id) {
            Flux::toast('Plan not found.', variant: 'danger');
            return;
        }

        // Check if plan has revisions
        if ($plan->revisions()->count() > 0) {
            Flux::toast('Cannot delete plan with existing revisions. Delete all revisions first.', variant: 'danger');
            return;
        }

        $this->planToDelete = $plan;
        $this->showDeletePlanModal = true;
    }

    // Actually delete the plan
    public function deletePlan()
    {
        if (!$this->planToDelete) {
            Flux::toast('No plan selected for deletion.', variant: 'danger');
            return;
        }

        $planName = $this->planToDelete->name;
        $this->planToDelete->delete();

        $this->showDeletePlanModal = false;
        $this->planToDelete = null;

        Flux::toast("Plan '{$planName}' deleted successfully.", variant: 'success');
    }

    public function cancelDeletePlan()
    {
        $this->showDeletePlanModal = false;
        $this->planToDelete = null;
    }

    // Delete Revision - Show confirmation modal
    public function confirmDeleteRevision($revisionId)
    {
        $revision = ServicePlanRevision::find($revisionId);

        if (!$revision) {
            Flux::toast('Revision not found.', variant: 'danger');
            return;
        }

        // Check if revision belongs to company's plan
        if ($revision->servicePlan->company_id !== $this->company->id) {
            Flux::toast('Revision not found.', variant: 'danger');
            return;
        }

        // Check if it's the current revision (but allow deletion if it's the only revision and still a draft)
        if ($revision->is_current) {
            $plan = $revision->servicePlan;
            $isOnlyRevision = $plan->revisions()->count() === 1;
            $isDraft = $revision->status === 'draft';

            if (!($isOnlyRevision && $isDraft)) {
                Flux::toast('Cannot delete the current revision. Make another revision current first.', variant: 'danger');
                return;
            }
        }

        // Check if revision has levels (only block for published/archived revisions)
        if ($revision->levels()->count() > 0 && $revision->status !== 'draft') {
            Flux::toast('Cannot delete published revision with existing levels. Archive the revision instead.', variant: 'danger');
            return;
        }

        $this->revisionToDelete = $revision;
        $this->showDeleteRevisionModal = true;
    }

    // Actually delete the revision
    public function deleteRevision()
    {
        if (!$this->revisionToDelete) {
            Flux::toast('No revision selected for deletion.', variant: 'danger');
            return;
        }

        $revisionName = $this->revisionToDelete->name;
        $this->revisionToDelete->delete();

        $this->showDeleteRevisionModal = false;
        $this->revisionToDelete = null;

        Flux::toast("Revision '{$revisionName}' deleted successfully.", variant: 'success');
    }

    public function cancelDeleteRevision()
    {
        $this->showDeleteRevisionModal = false;
        $this->revisionToDelete = null;
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



    public function openEditRevisionModal($revisionId)
    {
        $revision = ServicePlanRevision::find($revisionId);

        if (!$revision) {
            Flux::toast('Revision not found.', variant: 'danger');
            return;
        }

        $this->editRevisionForm = [
            'id' => $revision->id,
            'name' => $revision->name,
            'status' => $revision->status,
        ];

        $this->editingRevisionData = $revision;
        $this->showEditRevisionModal = true;
    }

    public function openEditPlanModal($planId)
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
        ];

        $this->showEditPlanModal = true;
    }

    // Form Reset Methods
    private function resetPlanForm()
    {
        $this->planForm = [
            'name' => '',
            'description' => '',
        ];
    }

    private function resetRevisionForm()
    {
        $this->revisionForm = [
            'service_plan_id' => null,
            'name' => '',
            'status' => 'draft',
        ];
    }

    private function resetEditRevisionForm()
    {
        $this->editRevisionForm = [
            'id' => null,
            'name' => '',
            'status' => 'draft',
        ];
    }

    private function resetEditPlanForm()
    {
        $this->editPlanForm = [
            'id' => null,
            'name' => '',
            'description' => '',
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
            return redirect()->route('company.service.plans.edit.revision', [
                'company' => $this->company,
                'plan' => $plan,
                'revision' => $revision
            ]);
        } else {
            return redirect()->route('company.service.plans.edit', [
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

        return redirect()->route('company.service.plans.edit.revision', [
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
            ->ordered()
            ->get();

        return view('livewire.company.service-plans-new', compact(
            'servicePlans'
        ))->with([
            'editingRevisionData' => $this->editingRevisionData,
            'revisionToDelete' => $this->revisionToDelete,
            'planToDelete' => $this->planToDelete
        ]);
    }
}
