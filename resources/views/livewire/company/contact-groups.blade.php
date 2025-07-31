<div class="flex h-full w-full flex-1 flex-col">
    <!-- Navigation -->
    <div class="bg-gray-50 dark:bg-gray-800 border-b border-neutral-200 dark:border-neutral-700 px-4 flex items-center gap-4">
    <flux:badge color="zinc">Company Contacts</flux:badge>

    <flux:navbar>
            <flux:navbar.item
                :href="route('company.contacts', ['company' => $company->id])"
                icon="user-group"
                wire:navigate
            >
                Manage Contacts
            </flux:navbar.item>
            <flux:navbar.item
                :href="route('company.contact-groups', ['company' => $company->id])"
                :current="true"
                icon="folder"
                wire:navigate
            >
                Groups
            </flux:navbar.item>
        </flux:navbar>
    </div>
    <div class="p-4 flex flex-col gap-4">

    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Contact Groups
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Organize contacts into groups for better management.
                </p>
            </div>
            <flux:button
                variant="primary"
                icon="plus"
                wire:click="openCreateGroupModal"
            >
                Create Group
            </flux:button>
        </div>
    </div>

    <!-- Contact Groups Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1">
        @if(count($contactGroups) > 0)
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($contactGroups as $group)
                        <div class="bg-white dark:bg-gray-900 border border-neutral-200 dark:border-neutral-700 rounded-lg p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1">
                                        <flux:badge variant="solid" color="{{ $group->color ?? 'blue' }}" class="w-4 h-4 p-0"></flux:badge>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $group->name }}</h3>
                                        @if($group->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $group->description }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-2">{{ $group->contacts->count() }} contact{{ $group->contacts->count() !== 1 ? 's' : '' }}</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <flux:button variant="subtle" size="sm" icon="pencil" wire:click="openEditGroupModal({{ $group->id }})">
                                        Edit
                                    </flux:button>
                                    <flux:button variant="danger" size="sm" icon="trash" wire:click="confirmDeleteGroup({{ $group->id }})">
                                        Delete
                                    </flux:button>
                                </div>
                            </div>

                            <!-- Group Contacts -->
                            @if($group->contacts->count() > 0)
                                <div class="space-y-2">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Contacts:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($group->contacts as $contact)
                                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $contact->name }}</span>
                                                <flux:button
                                                    variant="subtle"
                                                    size="xs"
                                                    icon="x-mark"
                                                    wire:click="confirmRemoveFromGroup({{ $group->id }}, {{ $contact->id }})"
                                                ></flux:button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">No contacts in this group yet.</p>
                            @endif

                            <!-- Add Contacts Button -->
                            <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                                <flux:button variant="outline" size="sm" icon="plus" wire:click="openManageContactsModal({{ $group->id }})">
                                    Add Contacts
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <flux:icon name="folder" class="h-8 w-8 text-neutral-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No contact groups yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Get started by creating your first contact group to organize your contacts.
                </p>
                <div class="mt-6">
                    <flux:button
                        variant="primary"
                        icon="plus"
                        wire:click="openCreateGroupModal"
                    >
                        Create Your First Group
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <!-- Create Group Modal -->
    <flux:modal variant="flyout" wire:model.self="showCreateGroupModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create Contact Group</flux:heading>
                <flux:text class="mt-2">Create a new group to organize your contacts.</flux:text>
            </div>
            <form wire:submit="createGroup" class="space-y-6">
                <flux:input label="Group Name" wire:model="groupName" required placeholder="Enter group name" />
                <flux:textarea label="Description (Optional)" wire:model="groupDescription" placeholder="Enter group description" rows="3" />

                <div>
                    <flux:radio.group wire:model="groupColor" label="Group Color" variant="pills" class="flex-wrap">
                        @foreach($colorOptions as $colorKey => $colorData)
                            <flux:radio value="{{ $colorKey }}">
                                <flux:badge color="{{ $colorKey }}" size="sm">{{ $colorData['name'] }}</flux:badge>
                            </flux:radio>
                        @endforeach
                    </flux:radio.group>
                    @error('groupColor')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeCreateGroupModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Create Group</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Group Modal -->
    <flux:modal variant="flyout" wire:model.self="showEditGroupModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Contact Group</flux:heading>
                <flux:text class="mt-2">Update the group information.</flux:text>
            </div>
            <form wire:submit="updateGroup" class="space-y-6">
                <flux:input label="Group Name" wire:model="editGroupName" required placeholder="Enter group name" />
                <flux:textarea label="Description (Optional)" wire:model="editGroupDescription" placeholder="Enter group description" rows="3" />

                <div>
                    <flux:radio.group wire:model="editGroupColor" label="Group Color" variant="pills" class="flex-wrap">
                        @foreach($colorOptions as $colorKey => $colorData)
                            <flux:radio value="{{ $colorKey }}">
                                <flux:badge color="{{ $colorKey }}" size="sm">{{ $colorData['name'] }}</flux:badge>
                            </flux:radio>
                        @endforeach
                    </flux:radio.group>
                    @error('editGroupColor')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeEditGroupModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Update Group</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Delete Group Modal -->
    <flux:modal wire:model.self="showDeleteGroupModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Contact Group</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete this group? This action cannot be undone.</flux:text>
            </div>
            @if($groupIdToDelete)
                @php
                    $group = $contactGroups->find($groupIdToDelete);
                @endphp
                @if($group)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex">
                            <flux:icon name="exclamation-triangle" class="h-5 w-5 text-red-400" />
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Delete Group: {{ $group->name }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>This will permanently delete the group. Contacts will not be deleted.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="closeDeleteGroupModal">Cancel</flux:button>
                <flux:button variant="danger" type="button" wire:click="deleteGroup">Delete Group</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Manage Contacts Modal -->
    <flux:modal variant="flyout" wire:model.self="showManageContactsModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add Contacts to Group</flux:heading>
                <flux:text class="mt-2">Select contacts to add to this group.</flux:text>
            </div>
            @if($availableContacts && $availableContacts->count() > 0)
                <form wire:submit="addContactsToGroup" class="space-y-6">
                    <div class="space-y-3">
                        <flux:text class="font-medium">Available Contacts:</flux:text>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach($availableContacts as $contact)
                                <label class="flex items-center space-x-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer">
                                    <input type="checkbox" wire:model="selectedContactIds" value="{{ $contact->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $contact->name }}</div>
                                        @if($contact->email_address)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $contact->email_address }}</div>
                                        @endif
                                        @if($contact->company_name)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $contact->company_name }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex">
                        <flux:spacer />
                        <flux:button variant="ghost" type="button" wire:click="closeManageContactsModal">Cancel</flux:button>
                        <flux:button variant="primary" type="submit">Add Contacts</flux:button>
                    </div>
                </form>
            @else
                <div class="text-center py-8">
                    <flux:icon name="user-group" class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                    <p class="text-gray-500 dark:text-gray-400">No available contacts to add to this group.</p>
                    <div class="mt-4">
                        <flux:button variant="ghost" type="button" wire:click="closeManageContactsModal">Close</flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>

    <!-- Remove Contact from Group Modal -->
    <flux:modal wire:model.self="showRemoveFromGroupModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove Contact from Group</flux:heading>
                <flux:text class="mt-2">Are you sure you want to remove this contact from the group?</flux:text>
            </div>
            @if($contactNameForRemoval && $groupNameForRemoval)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex">
                        <flux:icon name="exclamation-triangle" class="h-5 w-5 text-yellow-400" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Remove "{{ $contactNameForRemoval }}" from "{{ $groupNameForRemoval }}"
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>The contact will no longer be part of this group.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="showRemoveFromGroupModal = false">Cancel</flux:button>
                <flux:button variant="danger" type="button" wire:click="removeFromGroup">Remove Contact</flux:button>
            </div>
        </div>
    </flux:modal>
    </div>
</div>
