<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Company;
use App\Models\ContactGroup;
use Flux\Flux;

class ContactGroups extends Component
{
    public Company $company;

    // Contact group management properties
    public $groupName = '';
    public $groupDescription = '';
    public $groupColor = 'blue';
    public $showCreateGroupModal = false;
    public $showEditGroupModal = false;
    public $editingGroup = null;
    public $editGroupName = '';
    public $editGroupDescription = '';
    public $editGroupColor = 'blue';
    public $showDeleteGroupModal = false;
    public $groupIdToDelete = null;
    public $showManageContactsModal = false;
    public $selectedGroupId = null;
    public $selectedContactIds = [];

    // Remove contact from group confirmation
    public $showRemoveFromGroupModal = false;
    public $groupIdForRemoval = null;
    public $contactIdForRemoval = null;
    public $contactNameForRemoval = '';
    public $groupNameForRemoval = '';

    protected $rules = [
        'groupName' => 'required|string|max:255',
        'groupDescription' => 'nullable|string|max:1000',
        'groupColor' => 'required|string|in:red,orange,amber,yellow,lime,green,emerald,teal,cyan,sky,blue,indigo,violet,purple,fuchsia,pink,rose',
        'editGroupName' => 'required|string|max:255',
        'editGroupDescription' => 'nullable|string|max:1000',
        'editGroupColor' => 'required|string|in:red,orange,amber,yellow,lime,green,emerald,teal,cyan,sky,blue,indigo,violet,purple,fuchsia,pink,rose',
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

    // Contact group management methods
    public function openCreateGroupModal()
    {
        $this->reset(['groupName', 'groupDescription']);
        $this->groupColor = $this->getNextAvailableColor();
        $this->showCreateGroupModal = true;
    }

        /**
     * Get the next available color that hasn't been used by existing contact groups
     */
    private function getNextAvailableColor()
    {
        $availableColors = ['red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose'];

        // Get colors already used by existing contact groups in this company
        // Filter out null values (for older groups that existed before colors were added)
        $usedColors = $this->company->contactGroups()
            ->whereNotNull('color')
            ->pluck('color')
            ->filter()
            ->toArray();

        // Find the first available color that hasn't been used
        foreach ($availableColors as $color) {
            if (!in_array($color, $usedColors)) {
                return $color;
            }
        }

        // If all colors are used, cycle back to the beginning
        // This ensures we don't just default to blue when we have 17 colors available
        return $availableColors[0];
    }

    public function closeCreateGroupModal()
    {
        $this->showCreateGroupModal = false;
    }

    public function createGroup()
    {
        $this->validate([
            'groupName' => [
                'required',
                'string',
                'max:255',
                'unique:contact_groups,name,NULL,id,company_id,' . $this->company->id
            ],
            'groupDescription' => 'nullable|string|max:1000',
        ]);

        $this->company->contactGroups()->create([
            'name' => $this->groupName,
            'description' => $this->groupDescription,
            'color' => $this->groupColor,
        ]);

        $this->closeCreateGroupModal();
        Flux::toast(text: 'Contact group created successfully.', variant: 'success', duration: 3500);
    }

    public function openEditGroupModal($groupId)
    {
        $this->editingGroup = $this->company->contactGroups()->findOrFail($groupId);
        $this->editGroupName = $this->editingGroup->name;
        $this->editGroupDescription = $this->editingGroup->description;
        $this->editGroupColor = $this->editingGroup->color ?? 'blue';
        $this->showEditGroupModal = true;
    }

    public function closeEditGroupModal()
    {
        $this->showEditGroupModal = false;
        $this->editingGroup = null;
        $this->reset(['editGroupName', 'editGroupDescription', 'editGroupColor']);
    }

    public function updateGroup()
    {
        $this->validate([
            'editGroupName' => [
                'required',
                'string',
                'max:255',
                'unique:contact_groups,name,' . $this->editingGroup->id . ',id,company_id,' . $this->company->id
            ],
            'editGroupDescription' => 'nullable|string|max:1000',
        ]);

        if (!$this->editingGroup) {
            return;
        }

        $this->editingGroup->update([
            'name' => $this->editGroupName,
            'description' => $this->editGroupDescription,
            'color' => $this->editGroupColor,
        ]);

        $this->closeEditGroupModal();
        Flux::toast(text: 'Contact group updated successfully.', variant: 'success', duration: 3500);
    }

    public function confirmDeleteGroup($groupId)
    {
        $this->groupIdToDelete = $groupId;
        $this->showDeleteGroupModal = true;
    }

    public function closeDeleteGroupModal()
    {
        $this->showDeleteGroupModal = false;
        $this->groupIdToDelete = null;
    }

    public function deleteGroup()
    {
        if ($this->groupIdToDelete) {
            $group = $this->company->contactGroups()->findOrFail($this->groupIdToDelete);
            $group->delete();

            $this->closeDeleteGroupModal();
            Flux::toast(text: 'Contact group deleted successfully.', variant: 'success', duration: 3500);
        }
    }

    public function confirmRemoveFromGroup($groupId, $contactId)
    {
        $group = ContactGroup::find($groupId);
        $contact = $this->company->contacts()->where('id', $contactId)->first();

        if ($group && $contact && $group->company_id === $this->company->id) {
            $this->groupIdForRemoval = $groupId;
            $this->contactIdForRemoval = $contactId;
            $this->contactNameForRemoval = $contact->name;
            $this->groupNameForRemoval = $group->name;
            $this->showRemoveFromGroupModal = true;
        }
    }

    public function removeFromGroup()
    {
        if ($this->groupIdForRemoval && $this->contactIdForRemoval) {
            $group = ContactGroup::find($this->groupIdForRemoval);
            if ($group && $group->company_id === $this->company->id) {
                $group->contacts()->detach($this->contactIdForRemoval);

                $this->showRemoveFromGroupModal = false;
                $this->groupIdForRemoval = null;
                $this->contactIdForRemoval = null;
                $this->contactNameForRemoval = '';
                $this->groupNameForRemoval = '';

                Flux::toast(text: 'Contact removed from group.', variant: 'success', duration: 3500);
            }
        }
    }

    public function openManageContactsModal($groupId)
    {
        $this->selectedGroupId = $groupId;
        $this->selectedContactIds = [];
        $this->showManageContactsModal = true;
    }

    public function closeManageContactsModal()
    {
        $this->showManageContactsModal = false;
        $this->selectedGroupId = null;
        $this->selectedContactIds = [];
    }

    public function addContactsToGroup()
    {
        if ($this->selectedGroupId && count($this->selectedContactIds) > 0) {
            $group = ContactGroup::find($this->selectedGroupId);
            if ($group && $group->company_id === $this->company->id) {
                // Only attach contacts that aren't already in the group
                $existingContactIds = $group->contacts()->pluck('contact_id')->toArray();
                $newContactIds = array_diff($this->selectedContactIds, $existingContactIds);

                if (count($newContactIds) > 0) {
                    $group->contacts()->attach($newContactIds);
                    Flux::toast(text: count($newContactIds) . ' contact(s) added to group.', variant: 'success', duration: 3500);
                } else {
                    Flux::toast(text: 'Selected contacts are already in this group.', variant: 'warning', duration: 3500);
                }
            }
        }

        $this->closeManageContactsModal();
    }

    public function render()
    {
        // Load contact groups
        $contactGroups = $this->company->contactGroups()->with('contacts')->get();

        // Get available contacts for adding to groups (company contacts not in the selected group)
        $availableContacts = collect();
        if ($this->selectedGroupId) {
            $group = ContactGroup::find($this->selectedGroupId);
            if ($group) {
                $groupContactIds = $group->contacts()->pluck('contact_id')->toArray();
                $availableContacts = $this->company->contacts()->whereNotIn('id', $groupContactIds)->get();
            }
        }

        $colorOptions = [
            'red' => ['name' => 'Red', 'class' => 'bg-red-500'],
            'orange' => ['name' => 'Orange', 'class' => 'bg-orange-500'],
            'amber' => ['name' => 'Amber', 'class' => 'bg-amber-500'],
            'yellow' => ['name' => 'Yellow', 'class' => 'bg-yellow-500'],
            'lime' => ['name' => 'Lime', 'class' => 'bg-lime-500'],
            'green' => ['name' => 'Green', 'class' => 'bg-green-500'],
            'emerald' => ['name' => 'Emerald', 'class' => 'bg-emerald-500'],
            'teal' => ['name' => 'Teal', 'class' => 'bg-teal-500'],
            'cyan' => ['name' => 'Cyan', 'class' => 'bg-cyan-500'],
            'sky' => ['name' => 'Sky', 'class' => 'bg-sky-500'],
            'blue' => ['name' => 'Blue', 'class' => 'bg-blue-500'],
            'indigo' => ['name' => 'Indigo', 'class' => 'bg-indigo-500'],
            'violet' => ['name' => 'Violet', 'class' => 'bg-violet-500'],
            'purple' => ['name' => 'Purple', 'class' => 'bg-purple-500'],
            'fuchsia' => ['name' => 'Fuchsia', 'class' => 'bg-fuchsia-500'],
            'pink' => ['name' => 'Pink', 'class' => 'bg-pink-500'],
            'rose' => ['name' => 'Rose', 'class' => 'bg-rose-500'],
        ];

        return view('livewire.company.contact-groups', [
            'contactGroups' => $contactGroups,
            'availableContacts' => $availableContacts,
            'colorOptions' => $colorOptions,
        ])->layout('components.layouts.company');
    }
}
