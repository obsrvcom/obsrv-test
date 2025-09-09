<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Monitoring & Agents</h1>
        <div class="flex gap-3">
            <flux:button wire:click="refreshStatus" variant="subtle">
                <flux:icon.arrow-path class="w-4 h-4" />
                Refresh Status
            </flux:button>
            <flux:button wire:click="openPairingModal" variant="primary">
                <flux:icon.plus class="w-4 h-4" />
                Pair Agent
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <flux:input
                wire:model.live="search"
                placeholder="Search agents..."
                icon="magnifying-glass"
            />
            
            <flux:select wire:model.live="status" placeholder="All Statuses">
                <flux:select.option value="">All Statuses</flux:select.option>
                <flux:select.option value="online">Online</flux:select.option>
                <flux:select.option value="offline">Offline</flux:select.option>
                <flux:select.option value="provisioning">Provisioning</flux:select.option>
                <flux:select.option value="pairing">Pairing</flux:select.option>
            </flux:select>
            
            <flux:select wire:model.live="siteId" placeholder="All Sites">
                <flux:select.option value="">All Sites</flux:select.option>
                @foreach($sites as $site)
                    <flux:select.option value="{{ $site->id }}">{{ $site->name }}</flux:select.option>
                @endforeach
            </flux:select>
            
            @if($search || $status || $siteId)
                <flux:button wire:click="clearFilters" variant="subtle">
                    Clear Filters
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Agents</div>
            <div class="text-2xl font-bold">{{ $agents->total() }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Online</div>
            <div class="text-2xl font-bold text-green-600">
                {{ $agents->where('status', 'online')->count() }}
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Offline</div>
            <div class="text-2xl font-bold text-red-600">
                {{ $agents->where('status', 'offline')->count() }}
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Sites Monitored</div>
            <div class="text-2xl font-bold">{{ $sites->count() }}</div>
        </div>
    </div>

    {{-- Unpaired Agents Alert --}}
    @if($unpairedAgents->count() > 0)
        <flux:card class="mb-6 border-yellow-200 bg-yellow-50 dark:bg-yellow-900/20">
            <div class="flex items-start gap-3">
                <flux:icon.exclamation-triangle class="w-5 h-5 text-yellow-600 mt-0.5" />
                <div class="flex-1">
                    <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">
                        {{ $unpairedAgents->count() }} Unpaired Agent(s)
                    </h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        These agents are registered but not assigned to any site. Use their pairing codes to assign them.
                    </p>
                    <div class="mt-2 space-y-1">
                        @foreach($unpairedAgents as $unpaired)
                            <div class="text-sm">
                                <span class="font-mono">{{ $unpaired->device_id }}</span>
                                - Registered {{ $unpaired->created_at->diffForHumans() }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Agents Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($agents as $agent)
            <flux:card class="hover:shadow-lg transition-shadow cursor-pointer" wire:click="viewAgent({{ $agent->id }})">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full {{ $agent->status === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        <span class="text-sm font-medium">
                            {{ ucfirst($agent->status) }}
                        </span>
                    </div>
                    <flux:badge size="sm" variant="{{ $agent->type === 'raspberry-pi-5' ? 'primary' : 'subtle' }}">
                        {{ $agent->type }}
                    </flux:badge>
                </div>
                
                <h3 class="font-semibold text-lg mb-1">
                    {{ $agent->name ?? $agent->device_id }}
                </h3>
                
                <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <flux:icon.building-office class="w-4 h-4" />
                        {{ $agent->site->name }}
                    </div>
                    
                    @if($agent->ip_address)
                        <div class="flex items-center gap-2">
                            <flux:icon.globe-alt class="w-4 h-4" />
                            {{ $agent->ip_address }}
                        </div>
                    @endif
                    
                    @if($agent->last_heartbeat_at)
                        <div class="flex items-center gap-2">
                            <flux:icon.clock class="w-4 h-4" />
                            {{ $agent->last_heartbeat_at->diffForHumans() }}
                        </div>
                    @endif
                </div>
                
                @if($agent->latestHeartbeat)
                    <div class="mt-3 pt-3 border-t dark:border-gray-700">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            @if($agent->latestHeartbeat->metrics)
                                <div>
                                    <span class="text-gray-500">CPU:</span>
                                    <span class="font-medium">{{ $agent->latestHeartbeat->metrics['cpu'] ?? 'N/A' }}%</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Memory:</span>
                                    <span class="font-medium">{{ $agent->latestHeartbeat->metrics['memory'] ?? 'N/A' }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </flux:card>
        @empty
            <div class="col-span-full">
                <flux:card class="text-center py-12">
                    <flux:icon.server class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        No Agents Found
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        @if($search || $status || $siteId)
                            No agents match your filters. Try adjusting your search criteria.
                        @else
                            Get started by pairing your first agent to a site.
                        @endif
                    </p>
                    @if(!($search || $status || $siteId))
                        <flux:button wire:click="openPairingModal" variant="primary" class="mt-4">
                            <flux:icon.plus class="w-4 h-4" />
                            Pair Your First Agent
                        </flux:button>
                    @endif
                </flux:card>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($agents->hasPages())
        <div class="mt-6">
            {{ $agents->links() }}
        </div>
    @endif

    {{-- Pairing Modal --}}
    <flux:modal wire:model.self="showPairingModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Pair Agent to Site</flux:heading>
                <flux:text class="mt-2">Enter the pairing code shown on your agent's display</flux:text>
            </div>
            @if($pairingError)
                <div class="mb-2 p-3 rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                    {{ $pairingError }}
                </div>
            @endif
            
            <form wire:submit="pairAgent" class="space-y-6">
                <flux:input
                    wire:model="pairingCode"
                    label="Pairing Code"
                    placeholder="XXXX-XXXX-XXXX"
                    required
                />
                
                <flux:select
                    wire:model="selectedSiteId"
                    label="Select Site"
                    placeholder="Choose a site..."
                    required
                >
                    @foreach($sites as $site)
                        <flux:select.option value="{{ $site->id }}">{{ $site->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <h4 class="font-semibold text-sm text-blue-900 dark:text-blue-100 mb-2">
                    How to get a pairing code:
                </h4>
                <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                    <li>1. Power on your OBSRV agent device</li>
                    <li>2. Connect to the agent's web interface (port 3000)</li>
                    <li>3. The pairing code will be displayed on the screen</li>
                    <li>4. Enter the code here within 15 minutes</li>
                </ol>
            </div>
            
            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="closePairingModal" variant="subtle">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Pair Agent
                </flux:button>
            </div>
            </form>
        </div>
    </flux:modal>

    {{-- Agent Details Modal --}}
    <flux:modal wire:model.self="showAgentDetails" class="md:w-4xl">
        @if($selectedAgent)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $selectedAgent->name ?? $selectedAgent->device_id }}</flux:heading>
                    <flux:text class="mt-2">Agent Details and Status</flux:text>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold mb-3">Device Information</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm text-gray-500">Device ID</dt>
                                <dd class="font-mono">{{ $selectedAgent->device_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Thing Name</dt>
                                <dd class="font-mono">{{ $selectedAgent->thing_name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Type</dt>
                                <dd>{{ $selectedAgent->type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Firmware Version</dt>
                                <dd>{{ $selectedAgent->firmware_version ?? 'Unknown' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">IP Address</dt>
                                <dd>{{ $selectedAgent->ip_address ?? 'Unknown' }}</dd>
                            </div>
                        </dl>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold mb-3">Status & Assignment</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm text-gray-500">Status</dt>
                                <dd>
                                    <flux:badge variant="{{ $selectedAgent->status === 'online' ? 'success' : 'danger' }}">
                                        {{ ucfirst($selectedAgent->status) }}
                                    </flux:badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Site</dt>
                                <dd>{{ $selectedAgent->site->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Last Heartbeat</dt>
                                <dd>{{ $selectedAgent->last_heartbeat_at ? $selectedAgent->last_heartbeat_at->format('Y-m-d H:i:s') : 'Never' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Paired At</dt>
                                <dd>{{ $selectedAgent->paired_at ? $selectedAgent->paired_at->format('Y-m-d H:i:s') : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Provisioned At</dt>
                                <dd>{{ $selectedAgent->provisioned_at ? $selectedAgent->provisioned_at->format('Y-m-d H:i:s') : 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
                
                @if($selectedAgent->latestHeartbeat)
                    <div class="mt-6">
                        <h3 class="font-semibold mb-3">Latest Metrics</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @if($selectedAgent->latestHeartbeat->metrics)
                                @foreach($selectedAgent->latestHeartbeat->metrics as $key => $value)
                                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-3">
                                        <div class="text-sm text-gray-500">{{ ucfirst($key) }}</div>
                                        <div class="text-lg font-semibold">{{ $value }}{{ in_array($key, ['cpu', 'memory', 'disk']) ? '%' : '' }}</div>
                                    </div>
                                @endforeach
                            @endif
                            @if($selectedAgent->latestHeartbeat->uptime)
                                <div class="bg-gray-50 dark:bg-gray-800 rounded p-3">
                                    <div class="text-sm text-gray-500">Uptime</div>
                                    <div class="text-lg font-semibold">
                                        {{ floor($selectedAgent->latestHeartbeat->uptime / 86400) }}d
                                        {{ floor(($selectedAgent->latestHeartbeat->uptime % 86400) / 3600) }}h
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($selectedAgent->knx_monitors)
                    <div class="mt-6">
                        <h3 class="font-semibold mb-3">KNX Monitors</h3>
                        <div class="space-y-2">
                            @foreach($selectedAgent->knx_monitors as $monitor)
                                <div class="bg-gray-50 dark:bg-gray-800 rounded p-3">
                                    <div class="font-mono text-sm">{{ $monitor['ip'] ?? 'Unknown IP' }}</div>
                                    @if(isset($monitor['line_address']))
                                        <div class="text-xs text-gray-500">Line: {{ $monitor['line_address'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="flex">
                    <flux:spacer />
                    <flux:button wire:click="closeAgentDetails" variant="subtle">
                        Close
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>