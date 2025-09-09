<?php

namespace App\Livewire\Company;

use App\Models\Agent;
use App\Models\Company;
use App\Models\Site;
use App\Models\Telegram;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.company')]
class Telegrams extends Component
{
    use WithPagination;

    public Company $company;
    
    #[Url]
    public string $search = '';
    
    #[Url]
    public ?int $siteId = null;
    
    #[Url]
    public ?string $deviceId = null;
    
    #[Url]
    public string $service = '';
    
    #[Url]
    public string $timeRange = '1h'; // 1h, 6h, 24h, 7d, all

    public bool $showTelegramDetails = false;
    public ?Telegram $selectedTelegram = null;

    public function mount(Company $company)
    {
        // Check if user has access to this company
        if (!Auth::user()->companies->contains($company)) {
            abort(403);
        }
        
        $this->company = $company;
    }

    public function render()
    {
        $query = Telegram::query()
            ->whereHas('agent.site', function ($q) {
                $q->where('company_id', $this->company->id);
            })
            ->with(['agent.site'])
            ->latest('telegram_timestamp');

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('source', 'like', '%' . $this->search . '%')
                    ->orWhere('destination', 'like', '%' . $this->search . '%')
                    ->orWhere('data', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if ($this->deviceId) {
            $query->where('device_id', $this->deviceId);
        }

        if ($this->service) {
            $query->where('service', $this->service);
        }

        // Apply time range filter
        if ($this->timeRange !== 'all') {
            $hoursMap = [
                '1h' => 1,
                '6h' => 6,
                '24h' => 24,
                '7d' => 168,
            ];
            
            if (isset($hoursMap[$this->timeRange])) {
                $query->where('telegram_timestamp', '>=', now()->subHours($hoursMap[$this->timeRange]));
            }
        }

        $telegrams = $query->paginate(50);
        
        // Get sites for filter dropdown
        $sites = Site::where('company_id', $this->company->id)
            ->orderBy('name')
            ->get();
        
        // Get agents for filter dropdown
        $agents = Agent::whereHas('site', function ($q) {
            $q->where('company_id', $this->company->id);
        })
        ->with('site')
        ->orderBy('device_id')
        ->get();

        // Get available services for filter
        $services = Telegram::query()
            ->whereHas('agent.site', function ($q) {
                $q->where('company_id', $this->company->id);
            })
            ->distinct()
            ->pluck('service')
            ->filter()
            ->sort()
            ->values();

        return view('livewire.company.telegrams', [
            'telegrams' => $telegrams,
            'sites' => $sites,
            'agents' => $agents,
            'services' => $services,
        ]);
    }

    public function viewTelegram(Telegram $telegram)
    {
        // Verify the telegram belongs to this company
        if (!$telegram->agent || $telegram->agent->site->company_id !== $this->company->id) {
            abort(403);
        }
        
        $this->selectedTelegram = $telegram;
        $this->showTelegramDetails = true;
    }

    public function closeTelegramDetails()
    {
        $this->showTelegramDetails = false;
        $this->selectedTelegram = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->siteId = null;
        $this->deviceId = null;
        $this->service = '';
        $this->timeRange = '1h';
        $this->resetPage();
    }

    public function parseKnxData($data)
    {
        // Basic KNX data parsing - can be enhanced
        if (!$data) return 'No data';
        
        // Convert hex to decimal if it looks like hex
        if (preg_match('/^[0-9A-Fa-f]+$/', $data)) {
            $decimal = hexdec($data);
            return "0x{$data} ({$decimal})";
        }
        
        return $data;
    }

    public function formatService($service)
    {
        // Format service names to be more readable
        $serviceNames = [
            'GroupValueWrite' => 'Group Write',
            'GroupValueRead' => 'Group Read',
            'GroupValueResponse' => 'Group Response',
            'PhysicalAddressWrite' => 'Physical Write',
            'PhysicalAddressRead' => 'Physical Read',
            'PhysicalAddressResponse' => 'Physical Response',
            'AdcRead' => 'ADC Read',
            'AdcResponse' => 'ADC Response',
            'MemoryRead' => 'Memory Read',
            'MemoryResponse' => 'Memory Response',
            'MemoryWrite' => 'Memory Write',
        ];

        return $serviceNames[$service] ?? $service;
    }
}