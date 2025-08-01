@if($servicePlanGroups->count() > 0)
    <!-- Group Selection -->
    @if($servicePlanGroups->count() > 1)
        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                @foreach($servicePlanGroups as $group)
                    <button wire:click="selectGroup({{ $group->id }})"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                                   {{ $selectedGroup == $group->id
                                      ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                                      : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $group->name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if($selectedGroupData && $selectedGroupData->servicePlans->count() > 0)
        <!-- Service Plans Comparison Grid -->
        <div class="overflow-x-auto">
            <div class="min-w-full">
                <!-- Header Row with Plan Names -->
                <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
                    <div class="w-80 flex-shrink-0 px-4 py-3 font-medium text-gray-900 dark:text-white">
                        {{ $selectedGroupData->name }}
                    </div>
                    @foreach($selectedGroupData->servicePlans as $plan)
                        <div class="min-w-40 flex-1 px-4 py-3 text-center">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</div>
                            @if($plan->base_price_monthly)
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $plan->getFormattedPrice('monthly') }}/month
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Feature Categories and Features -->
                @php
                    $displayedCategories = collect();
                    foreach($selectedGroupData->servicePlans as $plan) {
                        foreach($plan->featureValues as $featureValue) {
                            if($featureValue->feature && $featureValue->feature->featureCategory) {
                                $displayedCategories->push($featureValue->feature->featureCategory);
                            }
                        }
                    }
                    $displayedCategories = $displayedCategories->unique('id')->sortBy('sort_order');
                @endphp

                @foreach($displayedCategories as $category)
                    <!-- Category Header -->
                    <div class="flex bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="w-80 flex-shrink-0 px-4 py-3 font-medium text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800">
                            {{ $category->name }}
                        </div>
                        @foreach($selectedGroupData->servicePlans as $plan)
                            <div class="min-w-40 flex-1 px-4 py-3"></div>
                        @endforeach
                    </div>

                    <!-- Features in this Category -->
                    @php
                        $categoryFeatures = collect();
                        foreach($selectedGroupData->servicePlans as $plan) {
                            foreach($plan->featureValues as $featureValue) {
                                if($featureValue->feature && $featureValue->feature->featureCategory && $featureValue->feature->featureCategory->id === $category->id) {
                                    $categoryFeatures->push($featureValue->feature);
                                }
                            }
                        }
                        $categoryFeatures = $categoryFeatures->unique('id')->sortBy('sort_order');
                    @endphp

                    @foreach($categoryFeatures as $feature)
                        <div class="flex border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900">
                            <div class="w-80 flex-shrink-0 px-4 py-3 text-gray-900 dark:text-white">
                                {{ $feature->name }}
                                @if($feature->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $feature->description }}</div>
                                @endif
                            </div>
                            @foreach($selectedGroupData->servicePlans as $plan)
                                @php
                                    $featureValue = $plan->featureValues->where('service_plan_feature_id', $feature->id)->first();
                                @endphp
                                <div class="min-w-40 flex-1 px-4 py-3 text-center">
                                    @if($featureValue)
                                        <span class="text-gray-900 dark:text-white">
                                            {{ $featureValue->formatted_value }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endforeach

                <!-- Pricing Summary -->
                @if($selectedGroupData->servicePlans->some(fn($plan) => $plan->base_price_monthly || $plan->base_price_quarterly || $plan->base_price_annually))
                    <!-- Recurring Costs Header -->
                    <div class="flex bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 mt-6">
                        <div class="w-80 flex-shrink-0 px-4 py-3 font-medium text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800">
                            Recurring Costs
                        </div>
                        @foreach($selectedGroupData->servicePlans as $plan)
                            <div class="min-w-40 flex-1 px-4 py-3"></div>
                        @endforeach
                    </div>

                    <!-- Monthly Pricing -->
                    @if($selectedGroupData->servicePlans->some(fn($plan) => $plan->base_price_monthly))
                        <div class="flex border-b border-gray-200 dark:border-gray-700">
                            <div class="w-80 flex-shrink-0 px-4 py-3 text-gray-900 dark:text-white">Cost Monthly</div>
                            @foreach($selectedGroupData->servicePlans as $plan)
                                <div class="min-w-40 flex-1 px-4 py-3 text-center">
                                    <span class="text-gray-900 dark:text-white font-medium">
                                        {{ $plan->getFormattedPrice('monthly') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Quarterly Pricing -->
                    @if($selectedGroupData->servicePlans->some(fn($plan) => $plan->base_price_quarterly))
                        <div class="flex border-b border-gray-200 dark:border-gray-700">
                            <div class="w-80 flex-shrink-0 px-4 py-3 text-gray-900 dark:text-white">Cost Quarterly</div>
                            @foreach($selectedGroupData->servicePlans as $plan)
                                <div class="min-w-40 flex-1 px-4 py-3 text-center">
                                    <span class="text-gray-900 dark:text-white font-medium">
                                        {{ $plan->getFormattedPrice('quarterly') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Annual Pricing -->
                    @if($selectedGroupData->servicePlans->some(fn($plan) => $plan->base_price_annually))
                        <div class="flex border-b border-gray-200 dark:border-gray-700">
                            <div class="w-80 flex-shrink-0 px-4 py-3 text-gray-900 dark:text-white">Cost Annually</div>
                            @foreach($selectedGroupData->servicePlans as $plan)
                                <div class="min-w-40 flex-1 px-4 py-3 text-center">
                                    <span class="text-gray-900 dark:text-white font-medium">
                                        {{ $plan->getFormattedPrice('annually') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Minimum Contract -->
                    @if($selectedGroupData->servicePlans->some(fn($plan) => $plan->minimum_contract_months))
                        <div class="flex border-b border-gray-200 dark:border-gray-700">
                            <div class="w-80 flex-shrink-0 px-4 py-3 text-gray-900 dark:text-white">Minimum Contract</div>
                            @foreach($selectedGroupData->servicePlans as $plan)
                                <div class="min-w-40 flex-1 px-4 py-3 text-center">
                                    <span class="text-gray-900 dark:text-white">
                                        @if($plan->minimum_contract_months)
                                            {{ $plan->minimum_contract_months }} Month{{ $plan->minimum_contract_months > 1 ? 's' : '' }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-end gap-3">
            <flux:button size="sm" variant="ghost" wire:click="openCreatePlanModal({{ $selectedGroupData->id }})">
                <flux:icon name="plus" class="size-4" />
                Add Plan
            </flux:button>
        </div>
    @else
        <!-- No Plans in Selected Group -->
        <div class="text-center py-16">
            <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                <flux:icon name="clipboard-document-list" class="h-8 w-8 text-neutral-400" />
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No plans yet</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                @if($selectedGroupData)
                    Create your first service plan in "{{ $selectedGroupData->name }}" to get started.
                @else
                    No plan group selected.
                @endif
            </p>
            @if($selectedGroupData)
                <div class="mt-6">
                    <flux:button size="sm" wire:click="openCreatePlanModal({{ $selectedGroupData->id }})">
                        <flux:icon name="plus" class="size-4" />
                        Create First Plan
                    </flux:button>
                </div>
            @endif
        </div>
    @endif
@else
    <!-- No Service Plan Groups -->
    <div class="text-center py-16">
        <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
            <flux:icon name="squares-plus" class="h-8 w-8 text-neutral-400" />
        </div>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No service plans yet</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            Create your first service plan group to organize and compare your different service offerings.
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateGroupModal">
                <flux:icon name="plus" class="size-4" />
                Create First Group
            </flux:button>
        </div>
    </div>
@endif
