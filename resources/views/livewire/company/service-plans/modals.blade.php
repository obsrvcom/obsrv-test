<!-- Create Plan Group Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateGroupModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Plan Group</flux:heading>
            <flux:text class="mt-2">Organize your service plans into logical groups.</flux:text>
        </div>

        <form wire:submit="createGroup" class="space-y-6">
            <flux:input label="Group Name" wire:model="groupForm.name" placeholder="e.g., Complete Care Options" required />
            @error('groupForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="groupForm.description" placeholder="Optional description of this plan group..." rows="3" />
            @error('groupForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div>
                <flux:checkbox wire:model="groupForm.is_active" label="Active" />
                <flux:text size="sm" class="mt-1 text-gray-600">Active groups are visible to customers.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateGroupModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Group</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Create Plan Modal -->
<flux:modal variant="flyout" wire:model.self="showCreatePlanModal" class="md:w-[600px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Service Plan</flux:heading>
            <flux:text class="mt-2">Add a new plan to your service offerings.</flux:text>
        </div>

        <form wire:submit="createPlan" class="space-y-6">
            <flux:select label="Plan Group" wire:model="planForm.service_plan_group_id" required>
                <option value="">Select a group...</option>
                @foreach($servicePlanGroups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </flux:select>
            @error('planForm.service_plan_group_id')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Plan Name" wire:model="planForm.name" placeholder="e.g., Level 1, Premium, Basic" required />
            @error('planForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="planForm.description" placeholder="Optional plan description..." rows="3" />
            @error('planForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:input label="Monthly Price (£)" type="number" step="0.01" wire:model="planForm.base_price_monthly" placeholder="0.00" />
                <flux:input label="Quarterly Price (£)" type="number" step="0.01" wire:model="planForm.base_price_quarterly" placeholder="0.00" />
                <flux:input label="Annual Price (£)" type="number" step="0.01" wire:model="planForm.base_price_annually" placeholder="0.00" />
            </div>

            <flux:input label="Minimum Contract (Months)" type="number" wire:model="planForm.minimum_contract_months" placeholder="12" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:checkbox wire:model="planForm.is_active" label="Active" />
                    <flux:text size="sm" class="mt-1 text-gray-600">Active plans are visible to customers.</flux:text>
                </div>

                <div>
                    <flux:checkbox wire:model="planForm.is_featured" label="Featured" />
                    <flux:text size="sm" class="mt-1 text-gray-600">Featured plans are highlighted to customers.</flux:text>
                </div>
            </div>

            <flux:input label="Plan Color" type="color" wire:model="planForm.color" />

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreatePlanModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Plan</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Create Feature Category Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateCategoryModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Feature Category</flux:heading>
            <flux:text class="mt-2">Group related features into logical categories.</flux:text>
        </div>

        <form wire:submit="createCategory" class="space-y-6">
            <flux:input label="Category Name" wire:model="categoryForm.name" placeholder="e.g., Telephone Support & Remote Diagnostics" required />
            @error('categoryForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="categoryForm.description" placeholder="Optional category description..." rows="3" />
            @error('categoryForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Category Color" type="color" wire:model="categoryForm.color" />

            <div>
                <flux:checkbox wire:model="categoryForm.is_active" label="Active" />
                <flux:text size="sm" class="mt-1 text-gray-600">Active categories are visible in plan comparisons.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateCategoryModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Category</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Create Feature Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateFeatureModal" class="md:w-[600px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Feature</flux:heading>
            <flux:text class="mt-2">Add a new configurable feature to your plans.</flux:text>
        </div>

        <form wire:submit="createFeature" class="space-y-6">
            <flux:select label="Feature Category" wire:model="featureForm.service_plan_feature_category_id" required>
                <option value="">Select a category...</option>
                @foreach($featureCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </flux:select>
            @error('featureForm.service_plan_feature_category_id')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Feature Name" wire:model="featureForm.name" placeholder="e.g., Remote Login Response Time" required />
            @error('featureForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="featureForm.description" placeholder="Optional feature description..." rows="3" />
            @error('featureForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:select label="Data Type" wire:model="featureForm.data_type">
                <option value="boolean">Boolean (Yes/No, Included/Not Included)</option>
                <option value="text">Text (Custom text values)</option>
                <option value="number">Number (Numeric values)</option>
                <option value="currency">Currency (Monetary values)</option>
                <option value="time">Time (Time periods, response times)</option>
                <option value="select">Select (Predefined options)</option>
            </flux:select>
            @error('featureForm.data_type')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Unit" wire:model="featureForm.unit" placeholder="e.g., hours, days, £, %" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:checkbox wire:model="featureForm.is_active" label="Active" />
                    <flux:text size="sm" class="mt-1 text-gray-600">Active features appear in plan comparisons.</flux:text>
                </div>

                <div>
                    <flux:checkbox wire:model="featureForm.affects_sla" label="Affects SLA" />
                    <flux:text size="sm" class="mt-1 text-gray-600">This feature impacts service level agreements.</flux:text>
                </div>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateFeatureModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Feature</flux:button>
            </div>
        </form>
    </div>
</flux:modal>
