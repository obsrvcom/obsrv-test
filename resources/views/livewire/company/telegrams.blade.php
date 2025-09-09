<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">KNX Telegrams</h1>
        <div class="flex items-center gap-3">
            <div class="text-sm text-gray-500">
                Real-time KNX network monitoring
            </div>
            <flux:button wire:click="$refresh" variant="subtle" size="sm">
                <flux:icon.arrow-path class="w-4 h-4" />
                Refresh
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <flux:input
                wire:model.live="search"
                placeholder="Search addresses, data..."
                icon="magnifying-glass"
            />
            
            <flux:select wire:model.live="timeRange">
                <flux:select.option value="1h">Last Hour</flux:select.option>
                <flux:select.option value="6h">Last 6 Hours</flux:select.option>
                <flux:select.option value="24h">Last 24 Hours</flux:select.option>
                <flux:select.option value="7d">Last 7 Days</flux:select.option>
                <flux:select.option value="all">All Time</flux:select.option>
            </flux:select>
            
            <flux:select wire:model.live="siteId" placeholder="All Sites">
                <flux:select.option value="">All Sites</flux:select.option>
                @foreach($sites as $site)
                    <flux:select.option value="{{ $site->id }}">{{ $site->name }}</flux:select.option>
                @endforeach
            </flux:select>
            
            <flux:select wire:model.live="deviceId" placeholder="All Agents">
                <flux:select.option value="">All Agents</flux:select.option>
                @foreach($agents as $agent)
                    <flux:select.option value="{{ $agent->device_id }}">
                        {{ $agent->device_id }} ({{ $agent->site->name }})
                    </flux:select.option>
                @endforeach
            </flux:select>
            
            <flux:select wire:model.live="service" placeholder="All Services">
                <flux:select.option value="">All Services</flux:select.option>
                @foreach($services as $service)
                    <flux:select.option value="{{ $service }}">{{ $this->formatService($service) }}</flux:select.option>
                @endforeach
            </flux:select>
            
            @if($search || $siteId || $deviceId || $service || $timeRange !== '1h')
                <flux:button wire:click="clearFilters" variant="subtle">
                    Clear Filters
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Telegrams</div>
            <div class="text-2xl font-bold">{{ number_format($telegrams->total()) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Active Agents</div>
            <div class="text-2xl font-bold text-blue-600">{{ $agents->count() }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Service Types</div>
            <div class="text-2xl font-bold text-green-600">{{ $services->count() }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Time Range</div>
            <div class="text-2xl font-bold">
                @if($timeRange === '1h') 1h
                @elseif($timeRange === '6h') 6h
                @elseif($timeRange === '24h') 24h
                @elseif($timeRange === '7d') 7d
                @else All
                @endif
            </div>
        </div>
    </div>

    {{-- Telegrams Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        @if($telegrams->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Timestamp
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Source
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Destination
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Service
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Data
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Agent
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Site
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($telegrams as $telegram)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 cursor-pointer" 
                                wire:click="viewTelegram({{ $telegram->id }})">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-mono">
                                        {{ $telegram->telegram_timestamp ? $telegram->telegram_timestamp->format('H:i:s.v') : 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $telegram->telegram_timestamp ? $telegram->telegram_timestamp->format('M d') : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm font-mono">
                                    {{ $telegram->source }}
                                </td>
                                <td class="px-4 py-3 text-sm font-mono">
                                    {{ $telegram->destination }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $this->formatService($telegram->service) }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">
                                        {{ $this->parseKnxData($telegram->data) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($telegram->agent)
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ $telegram->agent->status === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                            <span class="font-mono text-xs">{{ $telegram->device_id }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-500 text-xs">Unknown</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($telegram->agent && $telegram->agent->site)
                                        <div class="text-sm">{{ $telegram->agent->site->name }}</div>
                                    @else
                                        <span class="text-gray-500 text-xs">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <flux:icon.signal class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    No Telegrams Found
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    @if($search || $siteId || $deviceId || $service)
                        No telegrams match your current filters. Try adjusting your search criteria.
                    @else
                        No KNX telegrams have been received yet. Check that your agents are connected and monitoring KNX networks.
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($telegrams->hasPages())
        <div class="mt-6">
            {{ $telegrams->links() }}
        </div>
    @endif

    {{-- Telegram Details Modal --}}
    <flux:modal wire:model.self="showTelegramDetails" class="md:w-4xl">
        @if($selectedTelegram)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Telegram Details</flux:heading>
                    <flux:text class="mt-2">KNX telegram from {{ $selectedTelegram->source }} to {{ $selectedTelegram->destination }}</flux:text>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold mb-3">Telegram Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm text-gray-500">Timestamp</dt>
                                <dd class="font-mono">
                                    {{ $selectedTelegram->telegram_timestamp ? $selectedTelegram->telegram_timestamp->format('Y-m-d H:i:s.v') : 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Source Address</dt>
                                <dd class="font-mono text-lg">{{ $selectedTelegram->source }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Destination Address</dt>
                                <dd class="font-mono text-lg">{{ $selectedTelegram->destination }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Service Type</dt>
                                <dd>
                                    <flux:badge variant="primary">
                                        {{ $this->formatService($selectedTelegram->service) }}
                                    </flux:badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Data Payload</dt>
                                <dd class="font-mono bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded">
                                    {{ $this->parseKnxData($selectedTelegram->data) }}
                                </dd>
                            </div>
                            @if($selectedTelegram->message_code)
                                <div>
                                    <dt class="text-sm text-gray-500">Message Code</dt>
                                    <dd class="font-mono">{{ $selectedTelegram->message_code }}</dd>
                                </div>
                            @endif
                            @if($selectedTelegram->data_value)
                                <div>
                                    <dt class="text-sm text-gray-500">Data Value</dt>
                                    <dd class="font-mono">{{ $selectedTelegram->data_value }}</dd>
                                </div>
                            @endif
                            @if($selectedTelegram->direction)
                                <div>
                                    <dt class="text-sm text-gray-500">Direction</dt>
                                    <dd>{{ ucfirst($selectedTelegram->direction) }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold mb-3">Agent & Site Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm text-gray-500">Device ID</dt>
                                <dd class="font-mono">{{ $selectedTelegram->device_id }}</dd>
                            </div>
                            @if($selectedTelegram->agent)
                                <div>
                                    <dt class="text-sm text-gray-500">Agent Status</dt>
                                    <dd>
                                        <flux:badge variant="{{ $selectedTelegram->agent->status === 'online' ? 'success' : 'danger' }}">
                                            {{ ucfirst($selectedTelegram->agent->status) }}
                                        </flux:badge>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Agent Type</dt>
                                    <dd>{{ $selectedTelegram->agent->type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Agent IP</dt>
                                    <dd class="font-mono">{{ $selectedTelegram->agent->ip_address ?? 'Unknown' }}</dd>
                                </div>
                                @if($selectedTelegram->agent->site)
                                    <div>
                                        <dt class="text-sm text-gray-500">Site</dt>
                                        <dd class="font-semibold">{{ $selectedTelegram->agent->site->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Site Address</dt>
                                        <dd>{{ $selectedTelegram->agent->site->address ?? 'N/A' }}</dd>
                                    </div>
                                @endif
                            @endif
                            @if($selectedTelegram->batch_timestamp)
                                <div>
                                    <dt class="text-sm text-gray-500">Batch Timestamp</dt>
                                    <dd class="font-mono">{{ $selectedTelegram->batch_timestamp->format('Y-m-d H:i:s') }}</dd>
                                </div>
                            @endif
                            @if($selectedTelegram->sqs_message_id)
                                <div>
                                    <dt class="text-sm text-gray-500">SQS Message ID</dt>
                                    <dd class="font-mono text-xs">{{ $selectedTelegram->sqs_message_id }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
                
                {{-- Raw data section --}}
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <h4 class="font-semibold text-sm mb-2">Raw Telegram Data</h4>
                    <pre class="text-xs font-mono whitespace-pre-wrap break-all">{{ json_encode([
                        'source' => $selectedTelegram->source,
                        'destination' => $selectedTelegram->destination,
                        'service' => $selectedTelegram->service,
                        'data' => $selectedTelegram->data,
                        'timestamp' => $selectedTelegram->telegram_timestamp,
                        'device_id' => $selectedTelegram->device_id,
                        'site_id' => $selectedTelegram->site_id,
                    ], JSON_PRETTY_PRINT) }}</pre>
                </div>
                
                <div class="flex">
                    <flux:spacer />
                    <flux:button wire:click="closeTelegramDetails" variant="subtle">
                        Close
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>