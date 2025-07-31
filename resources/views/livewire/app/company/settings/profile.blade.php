<?php

use function Livewire\Volt\{state, mount, layout, rules, uses};
use Flux\Flux;
use Livewire\WithFileUploads;

uses([WithFileUploads::class]);

layout('components.layouts.company');

state([
    'name' => '',
    'description' => '',
    'avatar' => null,
    'isLoading' => false,
    'currentCompany' => null
]);

rules([
    'name' => 'required|string|max:255',
    'description' => 'nullable|string|max:1000',
    'avatar' => 'nullable|image|max:2048',
]);

mount(function() {
    $request = request();
    $user = auth()->user();

    // Get company from route model binding first (for company context pages)
    $routeCompany = $request->route('company');
    if ($routeCompany && is_object($routeCompany)) {
        $this->currentCompany = $routeCompany;
    } else {
        // Fallback to user's current company
        $this->currentCompany = $user ? ($user->currentCompanyFromRequest() ?? $user->currentCompany()) : null;
    }

    if ($this->currentCompany) {
        $this->name = $this->currentCompany->name;
        $this->description = $this->currentCompany->description ?? '';
    }
});

$removeAvatar = function() {
    try {
        if (!$this->currentCompany) {
            Flux::toast(text: 'Company not found.', variant: 'danger', duration: 3500);
            return;
        }

        if ($this->currentCompany->avatar) {
            \Storage::disk('public')->delete($this->currentCompany->avatar);
            $this->currentCompany->update(['avatar' => null]);

            // Dispatch event to update view selector
            $this->dispatch('company-avatar-updated', companyId: $this->currentCompany->id);

            Flux::toast(text: 'Avatar removed successfully.', variant: 'success', duration: 3500);
        }
    } catch (\Exception $e) {
        Flux::toast(text: 'Failed to remove avatar.', variant: 'danger', duration: 3500);
    }
};

$save = function() {
    // Validate the input
    $this->validate();

    $this->isLoading = true;

    try {
        if (!$this->currentCompany) {
            Flux::toast(text: 'Company not found.', variant: 'danger', duration: 3500);
            return;
        }

        // Admin access is already enforced by middleware

        $updateData = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        // Handle avatar upload
        if ($this->avatar) {
            // Delete old avatar if exists
            if ($this->currentCompany->avatar) {
                \Storage::disk('public')->delete($this->currentCompany->avatar);
            }

            // Store new avatar
            $avatarPath = $this->avatar->store('company-avatars', 'public');
            $updateData['avatar'] = $avatarPath;

            // Reset avatar input after saving
            $this->avatar = null;
        }

        $this->currentCompany->update($updateData);

        // Dispatch event to update view selector for any company profile changes
        $this->dispatch('company-avatar-updated', companyId: $this->currentCompany->id);

        Flux::toast(text: 'Company information updated successfully.', variant: 'success', duration: 3500);

    } catch (\Exception $e) {
        \Log::error('Company update failed', [
            'company_id' => $this->currentCompany->id ?? 'unknown',
            'error' => $e->getMessage(),
            'user_id' => auth()->id()
        ]);

        Flux::toast(text: 'Failed to update company information. Please try again.', variant: 'danger', duration: 3500);
    } finally {
        $this->isLoading = false;
    }
};

?>

<div class="flex h-full w-full flex-1 flex-col">
    <!-- Navigation -->
    <div class="bg-gray-50 dark:bg-gray-800 border-b border-neutral-200 dark:border-neutral-700 px-4 flex items-center gap-4">
        <flux:badge color="zinc">Company Settings</flux:badge>

        <flux:navbar>
            <flux:navbar.item
                :href="$currentCompany ? route('company.settings.profile', ['company' => $currentCompany->id]) : '#'"
                :current="true"
                icon="user"
                wire:navigate
            >
                Profile
            </flux:navbar.item>
            <flux:navbar.item
                :href="$currentCompany ? route('company.settings.chats', ['company' => $currentCompany->id]) : '#'"
                icon="chat-bubble-left-right"
                wire:navigate
            >
                Chats
            </flux:navbar.item>
        </flux:navbar>
    </div>

    <div class="p-4 flex flex-col gap-4">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Company Profile
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Update your company information.
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 p-6">
            <form wire:submit="save" class="space-y-6 max-w-lg">
                <!-- Company Name -->
                <flux:input
                    label="Company Name"
                    wire:model="name"
                    required
                    placeholder="Acme Corporation"
                />
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror

                <!-- Company Description -->
                <flux:textarea
                    label="Description (Optional)"
                    wire:model="description"
                    placeholder="Optional company description"
                    rows="3"
                />
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror

                <!-- Company Avatar -->
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Company Avatar</flux:label>

                        @if($currentCompany->avatar)
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                    <img src="{{ $currentCompany->getAvatarUrl() }}"
                                         alt="{{ $currentCompany->name }}"
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Current avatar</p>
                                    <flux:button
                                        wire:click="removeAvatar"
                                        variant="danger"
                                        size="sm"
                                        class="mt-1"
                                    >
                                        Remove Avatar
                                    </flux:button>
                                </div>
                            </div>
                        @endif

                        <flux:input
                            type="file"
                            wire:model="avatar"
                            accept="image/*"
                        />

                        @if($avatar)
                            <div class="mt-3">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Preview:</p>
                                <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                    <img src="{{ $avatar->temporaryUrl() }}"
                                         alt="Preview"
                                         class="w-full h-full object-cover">
                                </div>
                            </div>
                        @endif

                        <flux:description>
                            Upload a company logo or avatar (max 2MB). Recommended size: 200x200 pixels.
                        </flux:description>
                    </flux:field>

                    @error('avatar')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <flux:button
                        type="submit"
                        variant="primary"
                        :disabled="$isLoading"
                    >
                        @if($isLoading)
                            Saving...
                        @else
                            Save Changes
                        @endif
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
