<div class="flex h-full w-full flex-1 flex-col gap-4">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Plan Categories</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Organize your plans into categories like Service, Product, Maintenance, etc.</p>
            </div>
            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Add Category
            </flux:button>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        @if($categories->count() > 0)
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Plans
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Features
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                    @foreach($categories as $category)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    @if($category->icon)
                                        <div class="p-2 rounded-lg" style="background-color: {{ $category->color }}20;">
                                            <flux:icon name="{{ $category->icon }}" class="size-4" style="color: {{ $category->color }}" />
                                        </div>
                                    @else
                                        <div class="p-2 rounded-lg" style="background-color: {{ $category->color }}20;">
                                            <flux:icon name="folder" class="size-4" style="color: {{ $category->color }}" />
                                        </div>
                                    @endif
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-600 dark:text-gray-400 text-sm">
                                    {{ $category->description ?: 'No description' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $category->plans_count }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $category->feature_groups_count }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" variant="ghost" icon="eye" href="{{ route('company.plans.category', ['company' => $company, 'category' => $category]) }}">
                                        View
                                    </flux:button>
                                    <flux:dropdown>
                                        <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil" wire:click="editCategory({{ $category->id }})">
                                                Edit Category
                                            </flux:menu.item>
                                            <flux:menu.item icon="eye" href="{{ route('company.plans.category', ['company' => $company, 'category' => $category]) }}">
                                                View Plans
                                            </flux:menu.item>
                                            <flux:menu.item icon="cog-6-tooth" href="{{ route('company.features.category', ['company' => $company, 'category' => $category]) }}">
                                                Manage Features
                                            </flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $category->id }})">
                                                Delete Category
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center py-12">
                <flux:icon name="folder-plus" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No categories yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Start by creating categories to organize your plans.
                </p>
                <div class="mt-6">
                    <flux:button wire:click="openCreateModal" size="sm" icon="plus">
                        Create Category
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <!-- Create Category Modal -->
    <flux:modal variant="flyout" wire:model.self="showCreateModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create Category</flux:heading>
                <flux:text class="mt-2">Create a new category to organize your plans.</flux:text>
            </div>

            <form wire:submit="createCategory" class="space-y-6">
                <flux:input label="Category Name" wire:model="createForm.name" placeholder="e.g., Service, Product, Maintenance" required />
                @error('createForm.name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:textarea label="Description" wire:model="createForm.description" placeholder="Optional description of this category..." rows="3" />
                @error('createForm.description')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Color" wire:model="createForm.color" type="color" />
                    @error('createForm.color')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror

                    <flux:input label="Icon" wire:model="createForm.icon" placeholder="e.g., wrench-screwdriver" />
                    @error('createForm.icon')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="$set('showCreateModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Create Category</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Category Modal -->
    <flux:modal variant="flyout" wire:model.self="showEditModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Category</flux:heading>
                <flux:text class="mt-2">Update the category details.</flux:text>
            </div>

            <form wire:submit="updateCategory" class="space-y-6">
                <flux:input label="Category Name" wire:model="editForm.name" placeholder="e.g., Service, Product, Maintenance" required />
                @error('editForm.name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:textarea label="Description" wire:model="editForm.description" placeholder="Optional description of this category..." rows="3" />
                @error('editForm.description')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Color" wire:model="editForm.color" type="color" />
                    @error('editForm.color')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror

                    <flux:input label="Icon" wire:model="editForm.icon" placeholder="e.g., wrench-screwdriver" />
                    @error('editForm.icon')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="$set('showEditModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Update Category</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Delete Category Modal -->
    <flux:modal variant="flyout" wire:model.self="showDeleteModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Category</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete this category? This action cannot be undone.</flux:text>
            </div>

            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-400">
                    You are about to delete the category <strong>"{{ $deleteForm['name'] }}"</strong>.
                </p>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="deleteCategory">Delete Category</flux:button>
            </div>
        </div>
    </flux:modal>
</div>