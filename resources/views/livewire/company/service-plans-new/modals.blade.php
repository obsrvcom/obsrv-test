<!-- Create Service Plan Modal -->
<flux:modal variant="flyout" wire:model.self="showCreatePlanModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Service Plan</flux:heading>
            <flux:text class="mt-2">Create a new service plan that can have multiple revisions and levels.</flux:text>
        </div>

        <form wire:submit="createPlan" class="space-y-6">
            <flux:input label="Plan Name" wire:model="planForm.name" placeholder="e.g., Standard 2025, Premium Care 2024" required />
            @error('planForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="planForm.description" placeholder="Describe this service plan..." rows="3" />
            @error('planForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Plan Color" type="color" wire:model="planForm.color" />

            <div>
                <flux:checkbox wire:model="planForm.is_active" label="Active" />
                <flux:text size="sm" class="mt-1 text-gray-600">Active plans are visible to customers.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreatePlanModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Plan</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Create Revision Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateRevisionModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Revision</flux:heading>
            <flux:text class="mt-2">Create a new revision for this service plan.</flux:text>
        </div>

        <form wire:submit="createRevision" class="space-y-6">
            <flux:select label="Service Plan" wire:model="revisionForm.service_plan_id" required>
                <option value="">Select a plan...</option>
                @foreach($servicePlans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                @endforeach
            </flux:select>
            @error('revisionForm.service_plan_id')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Revision Name" wire:model="revisionForm.name" placeholder="e.g., v1.1, Q1 2025 Update" required />
            @error('revisionForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="revisionForm.description" placeholder="What's new in this revision..." rows="3" />
            @error('revisionForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:select label="Status" wire:model="revisionForm.status">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </flux:select>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateRevisionModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Revision</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Create Level Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateLevelModal" class="md:w-[600px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Level</flux:heading>
            <flux:text class="mt-2">Add a new level to this revision (e.g., Level 1, Level 2, Basic, Premium).</flux:text>
        </div>

        <form wire:submit="createLevel" class="space-y-6">
            @if($editingRevisionData)
                <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="text-sm">
                        <span class="font-medium">Adding to:</span>
                        {{ $editingRevisionData->servicePlan->name }} → {{ $editingRevisionData->name }}
                    </div>
                </div>
            @endif

            <flux:input label="Level Name" wire:model="levelForm.name" placeholder="e.g., Level 1, Basic, Premium" required />
            @error('levelForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="levelForm.description" placeholder="Describe this level..." rows="2" />
            @error('levelForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:input label="Monthly Price (£)" type="number" step="0.01" wire:model="levelForm.monthly_price" placeholder="0.00" />
                <flux:input label="Quarterly Price (£)" type="number" step="0.01" wire:model="levelForm.quarterly_price" placeholder="0.00" />
                <flux:input label="Annual Price (£)" type="number" step="0.01" wire:model="levelForm.annual_price" placeholder="0.00" />
            </div>

            <flux:input label="Minimum Contract (Months)" type="number" wire:model="levelForm.minimum_contract_months" placeholder="12" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:checkbox wire:model="levelForm.is_active" label="Active" />
                    <flux:text size="sm" class="mt-1 text-gray-600">Active levels are visible to customers.</flux:text>
                </div>

                <div>
                    <flux:checkbox wire:model="levelForm.is_featured" label="Featured" />
                    <flux:text size="sm" class="mt-1 text-gray-600">Featured levels are highlighted to customers.</flux:text>
                </div>
            </div>

            <flux:input label="Level Color" type="color" wire:model="levelForm.color" />

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateLevelModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Level</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

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

            <div>
                <flux:checkbox wire:model="featureGroupForm.is_active" label="Active" />
                <flux:text size="sm" class="mt-1 text-gray-600">Active groups are visible in plan comparisons.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateFeatureGroupModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Group</flux:button>
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
