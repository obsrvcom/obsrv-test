<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\ServicePlanNew;
use App\Models\ServicePlanRevision;
use App\Models\ServicePlanLevel;
use App\Models\ServicePlanFeatureGroupNew;
use App\Models\ServicePlanFeatureNew;
use App\Models\ServicePlanLevelFeatureValue;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux\Flux;

class PlanEdit extends Component
{
    public Company $company;
    public ServicePlanNew $plan;
    public ?ServicePlanRevision $revision = null;

    // Grid editing state
    public $editingCell = null; // Format: "level_id:feature_id"
    public $cellValue = '';
    public $cellIncluded = false;

    // Selected items for navigation
    public $selectedLevel = null;

    // Modal states
    public $showCreateRevisionModal = false;
    public $showEditRevisionModal = false;
    public $showCreateLevelModal = false;
    public $showEditLevelModal = false;
    public $showCreateFeatureGroupModal = false;
    public $showCreateFeatureModal = false;
    public $showEditFeatureModal = false;

    // Forms
    public $revisionForm = [
        'service_plan_id' => '',
        'name' => '',
        'description' => '',
        'status' => 'draft',
        'version_number' => 1,
    ];

    public $editRevisionForm = [
        'id' => '',
        'name' => '',
        'description' => '',
        'status' => 'draft',
    ];

    public $levelForm = [
        'service_plan_revision_id' => '',
        'name' => '',
        'description' => '',
        'is_active' => true,
        'is_featured' => false,
        'monthly_price' => '',
        'quarterly_price' => '',
        'annual_price' => '',
        'minimum_contract_months' => '',
        'color' => '#3B82F6',
    ];

    public $featureGroupForm = [
        'company_id' => '',
        'name' => '',
        'description' => '',
        'is_active' => true,
        'color' => '#6366F1',
    ];

    public $featureForm = [
        'feature_group_id' => '',
        'name' => '',
        'description' => '',
        'data_type' => 'boolean',
        'is_active' => true,
        'affects_sla' => false,
        'unit' => '',
        'options' => [],
    ];

    public function mount(ServicePlanNew $plan, ?ServicePlanRevision $revision = null)
    {
        $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

        // Ensure the plan belongs to the current company
        if ($plan->company_id !== $this->company->id) {
            abort(404);
        }

        $this->plan = $plan;

        if ($revision) {
            // Ensure the revision belongs to the plan
            if ($revision->service_plan_id !== $plan->id) {
                abort(404);
            }
            $this->revision = $revision;
        } else {
            // Auto-select current revision or latest revision
            $this->revision = $plan->getCurrentRevision() ?? $plan->getLatestRevision();
        }

        $this->featureGroupForm['company_id'] = $this->company->id;
    }

    // Revision Management
    public function createRevision()
    {
        $this->validate([
            'revisionForm.name' => 'required|string|max:255',
            'revisionForm.description' => 'nullable|string',
        ]);

        // Auto-increment version number
        $latestVersion = $this->plan->revisions()->max('version_number') ?? 0;
        $this->revisionForm['version_number'] = $latestVersion + 1;
        $this->revisionForm['service_plan_id'] = $this->plan->id;

        $revision = ServicePlanRevision::create($this->revisionForm);

        $this->showCreateRevisionModal = false;
        $this->resetRevisionForm();

        // Navigate to the new revision
        return redirect()->route('company.plans.edit.revision', [
            'company' => $this->company,
            'plan' => $this->plan,
            'revision' => $revision
        ]);
    }

    public function updateRevision()
    {
        $this->validate([
            'editRevisionForm.name' => 'required|string|max:255',
            'editRevisionForm.description' => 'nullable|string',
            'editRevisionForm.status' => 'required|in:draft,published,archived',
        ]);

        $revision = ServicePlanRevision::find($this->editRevisionForm['id']);

        if (!$revision || $revision->service_plan_id !== $this->plan->id) {
            Flux::toast('Revision not found.', variant: 'danger');
            return;
        }

        $revision->update([
            'name' => $this->editRevisionForm['name'],
            'description' => $this->editRevisionForm['description'],
            'status' => $this->editRevisionForm['status'],
        ]);

        $this->showEditRevisionModal = false;
        $this->resetEditRevisionForm();

        Flux::toast('Revision updated successfully!', variant: 'success');

        // Refresh the current revision if it was updated
        if ($this->revision && $this->revision->id === $revision->id) {
            $this->revision = $revision->fresh();
        }
    }

    // Level Management
    public function createLevel()
    {
        $this->validate([
            'levelForm.name' => 'required|string|max:255',
            'levelForm.description' => 'nullable|string',
            'levelForm.monthly_price' => 'nullable|numeric|min:0',
            'levelForm.quarterly_price' => 'nullable|numeric|min:0',
            'levelForm.annual_price' => 'nullable|numeric|min:0',
            'levelForm.minimum_contract_months' => 'nullable|integer|min:1',
        ]);

        // Clean up empty string values to null before creating
        $levelData = $this->levelForm;
        foreach (['monthly_price', 'quarterly_price', 'annual_price', 'minimum_contract_months'] as $field) {
            if ($levelData[$field] === '' || $levelData[$field] === null) {
                $levelData[$field] = null;
            }
        }

        if (!$this->revision) {
            Flux::toast('Please select a revision first.', variant: 'danger');
            return;
        }

        $levelData['service_plan_revision_id'] = $this->revision->id;
        $level = $this->revision->levels()->create(array_merge(
            $levelData,
            ['sort_order' => $this->revision->levels()->count()]
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

    // Modal controls
    public function openCreateRevisionModal()
    {
        $this->resetRevisionForm();
        $this->revisionForm['service_plan_id'] = $this->plan->id;
        $this->showCreateRevisionModal = true;
    }

    public function openEditRevisionModal($revisionId = null)
    {
        $revisionId = $revisionId ?? $this->revision?->id;

        if (!$revisionId) {
            Flux::toast('No revision selected.', variant: 'danger');
            return;
        }

        $revision = ServicePlanRevision::find($revisionId);

        if (!$revision || $revision->service_plan_id !== $this->plan->id) {
            Flux::toast('Revision not found.', variant: 'danger');
            return;
        }

        $this->editRevisionForm = [
            'id' => $revision->id,
            'name' => $revision->name,
            'description' => $revision->description ?? '',
            'status' => $revision->status,
        ];

        $this->showEditRevisionModal = true;
    }

    public function openCreateLevelModal($revisionId = null)
    {
        $this->resetLevelForm();
        if ($revisionId) {
            $this->levelForm['service_plan_revision_id'] = $revisionId;
        } elseif ($this->revision) {
            $this->levelForm['service_plan_revision_id'] = $this->revision->id;
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

    // Form resets
    public function resetRevisionForm()
    {
        $this->revisionForm = [
            'service_plan_id' => $this->plan->id,
            'name' => '',
            'description' => '',
            'status' => 'draft',
            'version_number' => 1,
        ];
    }

    public function resetEditRevisionForm()
    {
        $this->editRevisionForm = [
            'id' => '',
            'name' => '',
            'description' => '',
            'status' => 'draft',
        ];
    }

    public function resetLevelForm()
    {
        $this->levelForm = [
            'service_plan_revision_id' => $this->revision?->id ?? '',
            'name' => '',
            'description' => '',
            'is_active' => true,
            'is_featured' => false,
            'monthly_price' => '',
            'quarterly_price' => '',
            'annual_price' => '',
            'minimum_contract_months' => '',
            'color' => '#3B82F6',
        ];
    }

    public function resetFeatureGroupForm()
    {
        $this->featureGroupForm = [
            'company_id' => $this->company->id,
            'name' => '',
            'description' => '',
            'is_active' => true,
            'color' => '#6366F1',
        ];
    }

    public function resetFeatureForm()
    {
        $this->featureForm = [
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

    // Placeholder methods for functionality
    public function publishRevision($revisionId)
    {
        Flux::toast('Publish revision functionality coming soon.', variant: 'info');
    }

    public function archiveRevision($revisionId)
    {
        Flux::toast('Archive revision functionality coming soon.', variant: 'info');
    }

    // Modal states for level editing
    public $editLevelForm = [
        'id' => null,
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

    public function editLevel($levelId)
    {
        $level = ServicePlanLevel::find($levelId);

        if (!$level || !$this->revision || $level->service_plan_revision_id !== $this->revision->id) {
            Flux::toast('Level not found or invalid.', variant: 'danger');
            return;
        }

        $this->editLevelForm = [
            'id' => $level->id,
            'name' => $level->name,
            'description' => $level->description ?? '',
            'monthly_price' => $level->monthly_price,
            'quarterly_price' => $level->quarterly_price,
            'annual_price' => $level->annual_price,
            'minimum_contract_months' => $level->minimum_contract_months,
            'is_active' => $level->is_active,
            'is_featured' => $level->is_featured,
            'color' => $level->color ?? '#3B82F6',
        ];

        $this->showEditLevelModal = true;
    }

    public function updateLevel()
    {
        $this->validate([
            'editLevelForm.name' => 'required|string|max:255',
            'editLevelForm.description' => 'nullable|string',
            'editLevelForm.monthly_price' => 'nullable|numeric|min:0',
            'editLevelForm.quarterly_price' => 'nullable|numeric|min:0',
            'editLevelForm.annual_price' => 'nullable|numeric|min:0',
            'editLevelForm.minimum_contract_months' => 'nullable|integer|min:1',
        ]);

        $level = ServicePlanLevel::find($this->editLevelForm['id']);

        if (!$level || !$this->revision || $level->service_plan_revision_id !== $this->revision->id) {
            Flux::toast('Level not found or invalid.', variant: 'danger');
            return;
        }

        // Clean up empty string values to null before updating
        $updateData = $this->editLevelForm;
        foreach (['monthly_price', 'quarterly_price', 'annual_price', 'minimum_contract_months'] as $field) {
            if ($updateData[$field] === '' || $updateData[$field] === null) {
                $updateData[$field] = null;
            }
        }

        $level->update([
            'name' => $updateData['name'],
            'description' => $updateData['description'],
            'monthly_price' => $updateData['monthly_price'],
            'quarterly_price' => $updateData['quarterly_price'],
            'annual_price' => $updateData['annual_price'],
            'minimum_contract_months' => $updateData['minimum_contract_months'],
            'is_active' => $updateData['is_active'],
            'is_featured' => $updateData['is_featured'],
            'color' => $updateData['color'],
        ]);

        $this->showEditLevelModal = false;
        $this->resetEditLevelForm();

        Flux::toast('Level updated successfully!', variant: 'success');
    }

    public function resetEditLevelForm()
    {
        $this->editLevelForm = [
            'id' => null,
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

    public function deleteLevel($levelId)
    {
        Flux::toast('Delete level functionality coming soon.', variant: 'info');
    }

    public function manageLevelFeatures($levelId)
    {
        Flux::toast('Manage level features functionality coming soon.', variant: 'info');
    }

    public function editFeatureGroup($featureGroupId)
    {
        Flux::toast('Edit feature group functionality coming soon.', variant: 'info');
    }

    // Feature editing modals
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

    public function editFeature($featureId)
    {
        $feature = ServicePlanFeatureNew::find($featureId);

        if (!$feature || $feature->featureGroup->company_id !== $this->company->id) {
            Flux::toast('Feature not found or access denied.', variant: 'danger');
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

        if (!$feature || $feature->featureGroup->company_id !== $this->company->id) {
            Flux::toast('Feature not found or access denied.', variant: 'danger');
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

    // Grid editing functionality
    public function startEditingCell($levelId, $featureId)
    {
        $this->editingCell = $levelId . ':' . $featureId;

        // Load current value
        $featureValue = ServicePlanLevelFeatureValue::where('service_plan_level_id', $levelId)
            ->where('feature_id', $featureId)
            ->first();

        if ($featureValue) {
            $this->cellValue = $featureValue->value ?? '';
            $this->cellIncluded = $featureValue->is_included;
        } else {
            $this->cellValue = '';
            $this->cellIncluded = false;
        }
    }

    public function saveCellValue()
    {
        if (!$this->editingCell) {
            return;
        }

        [$levelId, $featureId] = explode(':', $this->editingCell);

        $level = ServicePlanLevel::find($levelId);
        $feature = ServicePlanFeatureNew::find($featureId);

        if (!$level || !$feature) {
            Flux::toast('Invalid level or feature.', variant: 'danger');
            return;
        }

        // Validate that level belongs to current plan revision
        if (!$this->revision || $level->service_plan_revision_id !== $this->revision->id) {
            Flux::toast('Level does not belong to current revision.', variant: 'danger');
            return;
        }

        // Find or create feature value
        $featureValue = ServicePlanLevelFeatureValue::updateOrCreate(
            [
                'service_plan_level_id' => $levelId,
                'feature_id' => $featureId,
            ],
            [
                'value' => $feature->isBoolean() ? null : $this->cellValue,
                'is_included' => $feature->isBoolean() ? $this->cellIncluded : !empty($this->cellValue),
                'display_value' => null, // Let the model handle formatting
            ]
        );

        $this->editingCell = null;
        $this->cellValue = '';
        $this->cellIncluded = false;

        Flux::toast('Feature value updated successfully!', variant: 'success');
    }

    public function cancelCellEdit()
    {
        $this->editingCell = null;
        $this->cellValue = '';
        $this->cellIncluded = false;
    }

    public function deleteLevelFromGrid($levelId)
    {
        $level = ServicePlanLevel::find($levelId);

        if (!$level || !$this->revision || $level->service_plan_revision_id !== $this->revision->id) {
            Flux::toast('Level not found or invalid.', variant: 'danger');
            return;
        }

        $levelName = $level->name;
        $level->delete();

        Flux::toast("Level '{$levelName}' deleted successfully.", variant: 'success');
    }

    #[Layout('components.layouts.company')]
    public function render()
    {
        // Get feature groups for this plan's category
        $featureGroups = collect();
        if ($this->plan->category) {
            $featureGroups = $this->plan->category->featureGroups()
                ->with(['features' => function($query) {
                    $query->active()->ordered();
                }])
                ->active()
                ->ordered()
                ->get();
        }

        // Load the plan with all its revisions
        $planData = ServicePlanNew::with([
            'revisions' => function($query) {
                $query->orderBy('version_number', 'desc');
            }
        ])->find($this->plan->id);

        // Load the current revision with its levels
        $revisionData = null;
        if ($this->revision) {
            $revisionData = ServicePlanRevision::with([
                'levels' => function($query) {
                    $query->active()->ordered();
                }
            ])->find($this->revision->id);
        }

        return view('livewire.company.plan-edit', compact(
            'featureGroups',
            'planData',
            'revisionData'
        ));
    }
}
