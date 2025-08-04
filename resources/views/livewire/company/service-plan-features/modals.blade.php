<!-- Create Feature Group Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateFeatureGroupModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Feature Group</flux:heading>
            <flux:text class="mt-2">Group related features together for better organization.</flux:text>
        </div>

        <form wire:submit="createFeatureGroup" class="space-y-6">
            <flux:input label="Group Name" wire:model="featureGroupForm.name" placeholder="e.g., Telephone Support & Remote Diagnostics" required />
            @error('featureGroupForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="featureGroupForm.description" placeholder="Describe this feature group..." rows="3" />
            @error('featureGroupForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Group Color" type="color" wire:model="featureGroupForm.color" />

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateFeatureGroupModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Group</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Edit Feature Group Modal -->
<flux:modal variant="flyout" wire:model.self="showEditFeatureGroupModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Edit Feature Group</flux:heading>
            <flux:text class="mt-2">Update the feature group details.</flux:text>
        </div>

        <form wire:submit="updateFeatureGroup" class="space-y-6">
            <flux:input label="Group Name" wire:model="featureGroupForm.name" placeholder="e.g., Telephone Support & Remote Diagnostics" required />
            @error('featureGroupForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="featureGroupForm.description" placeholder="Describe this feature group..." rows="3" />
            @error('featureGroupForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Group Color" type="color" wire:model="featureGroupForm.color" />

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showEditFeatureGroupModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Update Group</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Create Feature Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateFeatureModal" class="md:w-[600px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Feature</flux:heading>
            <flux:text class="mt-2">Add a new configurable feature to your service plans.</flux:text>
        </div>

        <form wire:submit="createFeature" class="space-y-6">
            <flux:select label="Feature Group" wire:model="featureForm.feature_group_id" required>
                <option value="">Select a group...</option>
                @foreach($featureGroups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </flux:select>
            @error('featureForm.feature_group_id')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Feature Name" wire:model="featureForm.name" placeholder="e.g., Remote Login Response Time" required />
            @error('featureForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="featureForm.description" placeholder="Describe this feature..." rows="3" />
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

<!-- Edit Feature Modal -->
<flux:modal variant="flyout" wire:model.self="showEditFeatureModal" class="md:w-[600px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Edit Feature</flux:heading>
            <flux:text class="mt-2">Update the feature details.</flux:text>
        </div>

        <form wire:submit="updateFeature" class="space-y-6">
            <flux:select label="Feature Group" wire:model="featureForm.feature_group_id" required>
                <option value="">Select a group...</option>
                @foreach($featureGroups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </flux:select>
            @error('featureForm.feature_group_id')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Feature Name" wire:model="featureForm.name" placeholder="e.g., Remote Login Response Time" required />
            @error('featureForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="featureForm.description" placeholder="Describe this feature..." rows="3" />
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
                <flux:button variant="ghost" type="button" wire:click="$set('showEditFeatureModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Update Feature</flux:button>
            </div>
        </form>
    </div>
</flux:modal>
