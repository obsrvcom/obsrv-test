<div class="flex h-full w-full flex-1 flex-col">
    <!-- Navigation -->
    <div class="bg-gray-50 dark:bg-gray-800 border-b border-neutral-200 dark:border-neutral-700 px-4 flex items-center gap-4">
        <flux:badge color="zinc">Company Contacts</flux:badge>

        <flux:navbar>
            <flux:navbar.item
                :href="route('company.contacts', ['company' => $company->id])"
                :current="true"
                icon="user-group"
                wire:navigate
            >
                Manage Contacts
            </flux:navbar.item>
            <flux:navbar.item
                :href="route('company.contact-groups', ['company' => $company->id])"
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
                    Company Contacts
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Manage your business contacts and relationships.
                </p>
            </div>
            <flux:button
                variant="primary"
                icon="plus"
                wire:click="openCreateModal"
            >
                Add Contact
            </flux:button>
        </div>
    </div>

    <!-- Contacts Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1">
        @if(count($contacts) > 0)
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-900">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Contact Name
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Company
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Job Title
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Groups
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($contacts as $contact)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                <flux:icon name="user" class="h-5 w-5 text-green-600 dark:text-green-400" />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $contact->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $contact->email_address ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $contact->company_name ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $contact->job_title ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($contact->contactGroups as $group)
                                            <flux:badge variant="solid" color="{{ $group->color ?? 'blue' }}" size="sm">
                                                {{ $group->name }}
                                            </flux:badge>
                                        @empty
                                            <span class="text-sm text-gray-400">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end space-x-2">
                                        <flux:button variant="subtle" size="sm" icon="pencil" wire:click="openEditModal({{ $contact->id }})">
                                            Edit
                                        </flux:button>
                                        <flux:button variant="danger" size="sm" icon="trash" wire:click="confirmDeleteContact({{ $contact->id }})">
                                            Delete
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <flux:icon name="users" class="h-8 w-8 text-neutral-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No contacts yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Get started by creating your first contact to track your business relationships.
                </p>
                <div class="mt-6">
                    <flux:button
                        variant="primary"
                        icon="plus"
                        wire:click="openCreateModal"
                    >
                        Add Your First Contact
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <!-- Create Contact Modal -->
    <flux:modal variant="flyout" wire:model.self="showCreateModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add New Contact</flux:heading>
                <flux:text class="mt-2">Add a new contact to your company.</flux:text>
            </div>
            @if($errorMessage)
                <div class="mb-2 p-3 rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                    {{ $errorMessage }}
                </div>
            @endif
            <form wire:submit="createContact" class="space-y-6">
                <flux:input label="Name" wire:model="name" required placeholder="Enter contact name" />
                <flux:input label="Email Address (Optional)" wire:model="emailAddress" type="email" placeholder="Enter email address" />
                <flux:input label="Company (Optional)" wire:model="companyName" placeholder="Enter company name" />
                <flux:input label="Job Title (Optional)" wire:model="jobTitle" placeholder="Enter job title" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeCreateModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Create Contact</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Contact Modal -->
    <flux:modal variant="flyout" wire:model.self="showEditModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Contact</flux:heading>
                <flux:text class="mt-2">Update the contact information.</flux:text>
            </div>
            @if($errorMessage)
                <div class="mb-2 p-3 rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                    {{ $errorMessage }}
                </div>
            @endif
            <form wire:submit="updateContact" class="space-y-6">
                <flux:input label="Name" wire:model="name" required placeholder="Enter contact name" />
                <flux:input label="Email Address (Optional)" wire:model="emailAddress" type="email" placeholder="Enter email address" />
                <flux:input label="Company (Optional)" wire:model="companyName" placeholder="Enter company name" />
                <flux:input label="Job Title (Optional)" wire:model="jobTitle" placeholder="Enter job title" />

                <!-- Contact Groups Section -->
                @if($contactGroups && $contactGroups->count() > 0)
                    <div class="space-y-3">
                        <flux:text class="font-medium">Contact Groups:</flux:text>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @foreach($contactGroups as $group)
                                <label class="flex items-center space-x-3 p-2 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer">
                                    <input type="checkbox" wire:model="selectedGroupIds" value="{{ $group->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <flux:badge variant="solid" color="{{ $group->color ?? 'blue' }}" class="w-3 h-3 p-0"></flux:badge>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $group->name }}</div>
                                        @if($group->description)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($group->description, 50) }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeEditModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Update Contact</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Delete Contact Modal -->
    <flux:modal wire:model.self="showDeleteModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Contact</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete this contact? This action cannot be undone.</flux:text>
            </div>
            @if($deletingContact)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex">
                        <flux:icon name="exclamation-triangle" class="h-5 w-5 text-red-400" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Delete Contact: {{ $deletingContact->name }}
                            </h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <p>This will permanently delete the contact and all associated data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="closeDeleteModal">Cancel</flux:button>
                <flux:button variant="danger" type="button" wire:click="deleteContact">Delete Contact</flux:button>
            </div>
        </div>
    </flux:modal>
    </div>
</div>
