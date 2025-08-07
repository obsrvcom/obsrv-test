<!-- Create Revision Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateRevisionModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Revision</flux:heading>
            <flux:text class="mt-2">Create a new revision for this service plan.</flux:text>
        </div>

        <form wire:submit="createRevision" class="space-y-6">
            <flux:input label="Revision Name" wire:model="revisionForm.name" placeholder="e.g., Version 2.0, Q2 2024 Update" required />
            @error('revisionForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="revisionForm.description" placeholder="Optional description of what's new in this revision..." rows="3" />
            @error('revisionForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateRevisionModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Revision</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Edit Revision Modal -->
<flux:modal variant="flyout" wire:model.self="showEditRevisionModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Edit Revision</flux:heading>
            <flux:text class="mt-2">Update the revision details.</flux:text>
        </div>

        <form wire:submit="updateRevision" class="space-y-6">
            <flux:input label="Revision Name" wire:model="editRevisionForm.name" placeholder="e.g., Version 2.0, Q2 2024 Update" required />
            @error('editRevisionForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="editRevisionForm.description" placeholder="Optional description of what's new in this revision..." rows="3" />
            @error('editRevisionForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:select label="Status" wire:model="editRevisionForm.status" required>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
            </flux:select>
            @error('editRevisionForm.status')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showEditRevisionModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Update Revision</flux:button>
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

            <flux:textarea label="Description" wire:model="levelForm.description" placeholder="Optional description of this level..." rows="3" />
            @error('levelForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="Monthly Price" wire:model="levelForm.monthly_price" type="number" step="0.01" min="0" placeholder="0.00" />
                @error('levelForm.monthly_price')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Quarterly Price" wire:model="levelForm.quarterly_price" type="number" step="0.01" min="0" placeholder="0.00" />
                @error('levelForm.quarterly_price')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="Annual Price" wire:model="levelForm.annual_price" type="number" step="0.01" min="0" placeholder="0.00" />
                @error('levelForm.annual_price')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Min Contract (months)" wire:model="levelForm.minimum_contract_months" type="number" min="1" placeholder="12" />
                @error('levelForm.minimum_contract_months')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <flux:input label="Color" wire:model="levelForm.color" type="color" />

            <div class="space-y-3">
                <flux:checkbox wire:model="levelForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active levels are visible to customers.</flux:text>

                <flux:checkbox wire:model="levelForm.is_featured" label="Featured" />
                <flux:text size="sm" class="text-gray-600">Featured levels are highlighted in comparisons.</flux:text>
            </div>

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
            <flux:text class="mt-2">Organize your service plan features into logical groups.</flux:text>
        </div>

        <form wire:submit="createFeatureGroup" class="space-y-6">
            <flux:input label="Group Name" wire:model="featureGroupForm.name" placeholder="e.g., Support Features, SLA Terms" required />
            @error('featureGroupForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="featureGroupForm.description" placeholder="Optional description of this feature group..." rows="3" />
            @error('featureGroupForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Color" wire:model="featureGroupForm.color" type="color" />

            <div>
                <flux:checkbox wire:model="featureGroupForm.is_active" label="Active" />
                <flux:text size="sm" class="mt-1 text-gray-600">Active groups are visible in service plans.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showCreateFeatureGroupModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create Group</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Edit Level Modal -->
<flux:modal variant="flyout" wire:model.self="showEditLevelModal" class="md:w-[600px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Edit Level</flux:heading>
            <flux:text class="mt-2">Update the level details.</flux:text>
        </div>

        <form wire:submit="updateLevel" class="space-y-6">
            <flux:input label="Level Name" wire:model="editLevelForm.name" placeholder="e.g., Level 1, Basic, Premium" required />
            @error('editLevelForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="editLevelForm.description" placeholder="Optional description of this level..." rows="3" />
            @error('editLevelForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="Monthly Price" wire:model="editLevelForm.monthly_price" type="number" step="0.01" min="0" placeholder="0.00" />
                @error('editLevelForm.monthly_price')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Quarterly Price" wire:model="editLevelForm.quarterly_price" type="number" step="0.01" min="0" placeholder="0.00" />
                @error('editLevelForm.quarterly_price')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="Annual Price" wire:model="editLevelForm.annual_price" type="number" step="0.01" min="0" placeholder="0.00" />
                @error('editLevelForm.annual_price')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Min Contract (months)" wire:model="editLevelForm.minimum_contract_months" type="number" min="1" placeholder="12" />
                @error('editLevelForm.minimum_contract_months')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <flux:input label="Color" wire:model="editLevelForm.color" type="color" />

            <div class="space-y-3">
                <flux:checkbox wire:model="editLevelForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active levels are visible to customers.</flux:text>

                <flux:checkbox wire:model="editLevelForm.is_featured" label="Featured" />
                <flux:text size="sm" class="text-gray-600">Featured levels are highlighted in comparisons.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showEditLevelModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Update Level</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Create Feature Modal -->
<flux:modal variant="flyout" wire:model.self="showCreateFeatureModal" class="md:w-[500px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Feature</flux:heading>
            <flux:text class="mt-2">Add a new feature to organize in your service plans.</flux:text>
        </div>

        <form wire:submit="createFeature" class="space-y-6">
            <flux:select label="Feature Group" wire:model="featureForm.feature_group_id" placeholder="Select a feature group" required>
                @foreach($featureGroups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </flux:select>
            @error('featureForm.feature_group_id')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:input label="Feature Name" wire:model="featureForm.name" placeholder="e.g., Response Time, Phone Support" required />
            @error('featureForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="featureForm.description" placeholder="Optional description of this feature..." rows="2" />
            @error('featureForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="Data Type" wire:model="featureForm.data_type" required>
                    <option value="boolean">Yes/No (Boolean)</option>
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="currency">Currency</option>
                    <option value="time">Time Duration</option>
                    <option value="select">Multiple Choice</option>
                </flux:select>
                @error('featureForm.data_type')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Unit" wire:model="featureForm.unit" placeholder="e.g., hours, GB, %" />
                @error('featureForm.unit')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="space-y-3">
                <flux:checkbox wire:model="featureForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active features are available for use in plans.</flux:text>

                <flux:checkbox wire:model="featureForm.affects_sla" label="Affects SLA" />
                <flux:text size="sm" class="text-gray-600">This feature impacts service level agreements.</flux:text>
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
<flux:modal variant="flyout" wire:model.self="showEditFeatureModal" class="md:w-[500px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Edit Feature</flux:heading>
            <flux:text class="mt-2">Update this feature's details, including SLA settings.</flux:text>
        </div>

        <form wire:submit="updateFeature" class="space-y-6">
            <flux:input label="Feature Name" wire:model="editFeatureForm.name" placeholder="e.g., Response Time, Phone Support" required />
            @error('editFeatureForm.name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <flux:textarea label="Description" wire:model="editFeatureForm.description" placeholder="Optional description of this feature..." rows="2" />
            @error('editFeatureForm.description')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="Data Type" wire:model="editFeatureForm.data_type" required>
                    <option value="boolean">Yes/No (Boolean)</option>
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="currency">Currency</option>
                    <option value="time">Time Duration</option>
                    <option value="select">Multiple Choice</option>
                </flux:select>
                @error('editFeatureForm.data_type')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Unit" wire:model="editFeatureForm.unit" placeholder="e.g., hours, GB, %" />
                @error('editFeatureForm.unit')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="space-y-4">
                <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:checkbox wire:model="editFeatureForm.affects_sla" />
                        <div>
                            <label class="text-sm font-medium text-orange-900 dark:text-orange-300">Affects SLA</label>
                            <p class="text-xs text-orange-800 dark:text-orange-400 mt-1">
                                Check this if this feature impacts service level agreements and customer commitments.
                            </p>
                        </div>
                    </div>
                </div>

                <flux:checkbox wire:model="editFeatureForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active features are available for use in plans.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showEditFeatureModal', false)">Cancel</flux:button>
                <flux:button variant="primary" type="submit">Update Feature</flux:button>
            </div>
        </form>
    </div>
</flux:modal>
