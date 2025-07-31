<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Company;
use App\Models\Site;
use Flux\Flux;

class Sites extends Component
{
    public Company $company;

    // Site management properties
    public $name = '';
    public $address = '';
    public $successMessage = null;
    public $errorMessage = null;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editingSite = null;
    public $deletingSite = null;

    // Site groups management
    public $selectedGroupIds = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:1000',
    ];

    public function mount($company = null)
    {
        // Handle route model binding
        if ($company instanceof Company) {
            $this->company = $company;
        } else {
            // Fallback to getting company from route or current company
            $routeCompany = request()->route('company');
            if ($routeCompany instanceof Company) {
                $this->company = $routeCompany;
            } else {
                $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
            }
        }
    }

    // Site management methods
    public function openCreateModal()
    {
        $this->reset(['name', 'address', 'successMessage', 'errorMessage']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function createSite()
    {
        $this->validate();

        $this->company->sites()->create([
            'name' => $this->name,
            'address' => $this->address,
        ]);

        $this->closeCreateModal();
        Flux::toast(text: 'Site created successfully.', variant: 'success', duration: 3500);
    }

    public function openEditModal($siteId)
    {
        $this->editingSite = $this->company->sites()->findOrFail($siteId);
        $this->name = $this->editingSite->name;
        $this->address = $this->editingSite->address;

        // Load current site groups
        $this->selectedGroupIds = $this->editingSite->siteGroups()->pluck('site_groups.id')->toArray();

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingSite = null;
        $this->reset(['name', 'address', 'selectedGroupIds']);
    }

    public function updateSite()
    {
        $this->validate();

        if (!$this->editingSite) {
            return;
        }

        $this->editingSite->update([
            'name' => $this->name,
            'address' => $this->address,
        ]);

        // Sync site groups
        $this->editingSite->siteGroups()->sync($this->selectedGroupIds);

        $this->closeEditModal();
        Flux::toast(text: 'Site updated successfully.', variant: 'success', duration: 3500);
    }

    public function confirmDeleteSite($siteId)
    {
        $this->deletingSite = $this->company->sites()->findOrFail($siteId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletingSite = null;
    }

    public function deleteSite()
    {
        if ($this->deletingSite) {
            $this->deletingSite->delete();
            $this->closeDeleteModal();
            Flux::toast(text: 'Site deleted successfully.', variant: 'success', duration: 3500);
        }
    }

    public function render()
    {
        $sites = $this->company->sites()->with('siteGroups')->orderBy('name')->get();
        $siteGroups = $this->company->siteGroups()->orderBy('name')->get();

        return view('livewire.company.sites', [
            'sites' => $sites,
            'siteGroups' => $siteGroups,
        ])->layout('components.layouts.company');
    }
}
