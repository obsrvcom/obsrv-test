<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\PlanCategory;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux\Flux;

class PlanCategories extends Component
{
    public Company $company;

    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    // Forms
    public $createForm = [
        'name' => '',
        'description' => '',
        'color' => '#3B82F6',
        'icon' => '',
    ];

    public $editForm = [
        'id' => null,
        'name' => '',
        'description' => '',
        'color' => '#3B82F6',
        'icon' => '',
    ];

    public $deleteForm = [
        'id' => null,
        'name' => '',
    ];

    public function mount()
    {
        $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
    }

    // Create Category
    public function createCategory()
    {
        $this->validate([
            'createForm.name' => 'required|string|max:255',
            'createForm.description' => 'nullable|string',
            'createForm.color' => 'required|string|max:7',
            'createForm.icon' => 'nullable|string|max:50',
        ]);

        $category = $this->company->planCategories()->create(array_merge(
            $this->createForm,
            [
                'sort_order' => $this->company->planCategories()->count(),
                'company_id' => $this->company->id,
            ]
        ));

        $this->showCreateModal = false;
        $this->resetCreateForm();

        Flux::toast('Category created successfully!', variant: 'success');
    }

    // Edit Category
    public function editCategory($categoryId)
    {
        $category = PlanCategory::find($categoryId);

        if (!$category || $category->company_id !== $this->company->id) {
            Flux::toast('Category not found.', variant: 'danger');
            return;
        }

        $this->editForm = [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description ?? '',
            'color' => $category->color,
            'icon' => $category->icon ?? '',
        ];

        $this->showEditModal = true;
    }

    public function updateCategory()
    {
        $this->validate([
            'editForm.name' => 'required|string|max:255',
            'editForm.description' => 'nullable|string',
            'editForm.color' => 'required|string|max:7',
            'editForm.icon' => 'nullable|string|max:50',
        ]);

        $category = PlanCategory::find($this->editForm['id']);

        if (!$category || $category->company_id !== $this->company->id) {
            Flux::toast('Category not found.', variant: 'danger');
            return;
        }

        $category->update([
            'name' => $this->editForm['name'],
            'description' => $this->editForm['description'],
            'color' => $this->editForm['color'],
            'icon' => $this->editForm['icon'],
        ]);

        $this->showEditModal = false;
        $this->resetEditForm();

        Flux::toast('Category updated successfully!', variant: 'success');
    }

    // Delete Category
    public function confirmDelete($categoryId)
    {
        $category = PlanCategory::find($categoryId);

        if (!$category || $category->company_id !== $this->company->id) {
            Flux::toast('Category not found.', variant: 'danger');
            return;
        }

        // Check if category has plans
        $plansCount = $category->plans()->count();
        if ($plansCount > 0) {
            Flux::toast("Cannot delete category with {$plansCount} plan(s). Move or delete plans first.", variant: 'danger');
            return;
        }

        $this->deleteForm = [
            'id' => $category->id,
            'name' => $category->name,
        ];

        $this->showDeleteModal = true;
    }

    public function deleteCategory()
    {
        $category = PlanCategory::find($this->deleteForm['id']);

        if (!$category || $category->company_id !== $this->company->id) {
            Flux::toast('Category not found.', variant: 'danger');
            return;
        }

        $categoryName = $category->name;
        $category->delete();

        $this->showDeleteModal = false;
        $this->resetDeleteForm();

        Flux::toast("Category '{$categoryName}' deleted successfully!", variant: 'success');
    }

    // Modal controls
    public function openCreateModal()
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    // Form resets
    public function resetCreateForm()
    {
        $this->createForm = [
            'name' => '',
            'description' => '',
            'color' => '#3B82F6',
            'icon' => '',
        ];
    }

    public function resetEditForm()
    {
        $this->editForm = [
            'id' => null,
            'name' => '',
            'description' => '',
            'color' => '#3B82F6',
            'icon' => '',
        ];
    }

    public function resetDeleteForm()
    {
        $this->deleteForm = [
            'id' => null,
            'name' => '',
        ];
    }

    #[Layout('components.layouts.company')]
    public function render()
    {
        $categories = $this->company->planCategories()
            ->withCount(['plans', 'featureGroups'])
            ->ordered()
            ->get();

        return view('livewire.company.plan-categories', compact('categories'));
    }
}