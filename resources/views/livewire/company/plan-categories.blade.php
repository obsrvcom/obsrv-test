<div class="flex h-full w-full flex-1 flex-col gap-4">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Plan Categories</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Organize your plans into categories like Service, Product, Maintenance, etc.</p>
            </div>
            <flux:button wire:click="openCreateModal" variant="primary">
                <flux:icon name="plus" class="size-4" />
                Add Category
            </flux:button>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($categories as $category)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        @if($category->icon)
                            <div class="p-2 rounded-lg" style="background-color: {{ $category->color }}20;">
                                <flux:icon name="{{ $category->icon }}" class="size-6" style="color: {{ $category->color }}" />
                            </div>
                        @else
                            <div class="p-2 rounded-lg" style="background-color: {{ $category->color }}20;">
                                <flux:icon name="folder" class="size-6" style="color: {{ $category->color }}" />
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $category->name }}</h3>
                            @if(!$category->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                    <flux:dropdown>
                        <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:menu.item icon="pencil" wire:click="editCategory({{ $category->id }})">
                                Edit Category
                            </flux:menu.item>
                            <flux:menu.item icon="eye" href="{{ route('company.plans.category', ['company' => $company, 'category' => $category]) }}">
                                View Plans
                            </flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $category->id }})">
                                Delete Category
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>

                @if($category->description)
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $category->description }}</p>
                @endif

                <div class="grid grid-cols-2 gap-4 text-center">
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $category->plans_count }}</div>
                        <div class="text-xs text-gray-500">Plans</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $category->feature_groups_count }}</div>
                        <div class="text-xs text-gray-500">Feature Groups</div>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <flux:button size="sm" variant="ghost" href="{{ route('company.plans.category', ['company' => $company, 'category' => $category]) }}" class="flex-1">
                        <flux:icon name="eye" class="size-4" />
                        View Plans
                    </flux:button>
                    <flux:button size="sm" variant="ghost" href="{{ route('company.features.category', ['company' => $company, 'category' => $category]) }}" class="flex-1">
                        <flux:icon name="cog-6-tooth" class="size-4" />
                        Features
                    </flux:button>
                </div>
            </div>
        @endforeach

        @if($categories->count() === 0)
            <div class="col-span-full text-center py-12">
                <flux:icon name="folder-plus" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No categories yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Start by creating categories to organize your plans.
                </p>
                <div class="mt-6">
                    <flux:button wire:click="openCreateModal" size="sm">
                        <flux:icon name="plus" class="size-4" />
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

                <flux:checkbox wire:model="createForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active categories are visible and can be used.</flux:text>

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

                <flux:checkbox wire:model="editForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active categories are visible and can be used.</flux:text>

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