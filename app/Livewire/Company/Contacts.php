<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Company;
use App\Models\Contact;
use Flux\Flux;

class Contacts extends Component
{
    public Company $company;

    // Contact management properties
    public $name = '';
    public $emailAddress = '';
    public $companyName = '';
    public $jobTitle = '';
    public $successMessage = null;
    public $errorMessage = null;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editingContact = null;
    public $deletingContact = null;
    public $selectedGroupIds = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'emailAddress' => 'nullable|email|max:255',
        'companyName' => 'nullable|string|max:255',
        'jobTitle' => 'nullable|string|max:255',
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

    // Contact management methods
    public function openCreateModal()
    {
        $this->reset(['name', 'emailAddress', 'companyName', 'jobTitle', 'successMessage', 'errorMessage']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function createContact()
    {
        $this->validate();

        $this->company->contacts()->create([
            'name' => $this->name,
            'email_address' => $this->emailAddress,
            'company_name' => $this->companyName,
            'job_title' => $this->jobTitle,
        ]);

        $this->closeCreateModal();
        Flux::toast(text: 'Contact created successfully.', variant: 'success', duration: 3500);
    }

    public function openEditModal($contactId)
    {
        $this->editingContact = $this->company->contacts()->findOrFail($contactId);
        $this->name = $this->editingContact->name;
        $this->emailAddress = $this->editingContact->email_address;
        $this->companyName = $this->editingContact->company_name;
        $this->jobTitle = $this->editingContact->job_title;
        $this->selectedGroupIds = $this->editingContact->contactGroups()->pluck('contact_group_id')->toArray();
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingContact = null;
        $this->reset(['name', 'emailAddress', 'companyName', 'jobTitle', 'selectedGroupIds']);
    }

    public function updateContact()
    {
        $this->validate();

        if (!$this->editingContact) {
            return;
        }

        $this->editingContact->update([
            'name' => $this->name,
            'email_address' => $this->emailAddress,
            'company_name' => $this->companyName,
            'job_title' => $this->jobTitle,
        ]);

        // Sync contact groups
        $this->editingContact->contactGroups()->sync($this->selectedGroupIds);

        $this->closeEditModal();
        Flux::toast(text: 'Contact updated successfully.', variant: 'success', duration: 3500);
    }

    public function confirmDeleteContact($contactId)
    {
        $this->deletingContact = $this->company->contacts()->findOrFail($contactId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletingContact = null;
    }

    public function deleteContact()
    {
        if ($this->deletingContact) {
            $this->deletingContact->delete();
            $this->closeDeleteModal();
            Flux::toast(text: 'Contact deleted successfully.', variant: 'success', duration: 3500);
        }
    }

    public function render()
    {
        $contacts = $this->company->contacts()->with('contactGroups')->orderBy('name')->get();
        $contactGroups = $this->company->contactGroups()->orderBy('name')->get();

        return view('livewire.company.contacts', [
            'contacts' => $contacts,
            'contactGroups' => $contactGroups,
        ])->layout('components.layouts.company');
    }
}
