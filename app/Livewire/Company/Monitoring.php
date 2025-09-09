<?php

namespace App\Livewire\Company;

use App\Models\Agent;
use App\Models\Company;
use App\Models\PairingCode;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.company')]
class Monitoring extends Component
{
    use WithPagination;

    public Company $company;
    
    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
    #[Url]
    public ?int $siteId = null;
    
    public bool $showPairingModal = false;
    public string $pairingCode = '';
    public ?int $selectedSiteId = null;
    public string $pairingError = '';
    
    public bool $showAgentDetails = false;
    public ?Agent $selectedAgent = null;

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
        $query = Agent::query()
            ->whereHas('site', function ($q) {
                $q->where('company_id', $this->company->id);
            })
            ->with(['site', 'latestHeartbeat']);

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('device_id', 'like', '%' . $this->search . '%')
                    ->orWhere('thing_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        $agents = $query->latest('last_heartbeat_at')->paginate(12);
        
        // Get sites for filter dropdown
        $sites = Site::where('company_id', $this->company->id)
            ->orderBy('name')
            ->get();
        
        // Get unpaired agents (registered but not assigned to a site)
        $unpairedAgents = Agent::whereNull('site_id')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('livewire.company.monitoring', [
            'agents' => $agents,
            'sites' => $sites,
            'unpairedAgents' => $unpairedAgents,
        ]);
    }

    public function openPairingModal()
    {
        $this->showPairingModal = true;
        $this->pairingCode = '';
        $this->selectedSiteId = null;
        $this->pairingError = '';
    }

    public function closePairingModal()
    {
        $this->showPairingModal = false;
        $this->pairingCode = '';
        $this->selectedSiteId = null;
        $this->pairingError = '';
    }

    public function pairAgent()
    {
        $this->validate([
            'pairingCode' => 'required|string',
            'selectedSiteId' => 'required|exists:sites,id',
        ]);

        // Verify the site belongs to this company
        $site = Site::where('id', $this->selectedSiteId)
            ->where('company_id', $this->company->id)
            ->first();
            
        if (!$site) {
            $this->pairingError = 'Invalid site selected.';
            return;
        }

        // Find the pairing code
        $pairingCodeRecord = PairingCode::where('code', $this->pairingCode)->first();
        
        if (!$pairingCodeRecord) {
            $this->pairingError = 'Invalid pairing code.';
            return;
        }
        
        if (!$pairingCodeRecord->isValid()) {
            $this->pairingError = $pairingCodeRecord->used 
                ? 'This pairing code has already been used.' 
                : 'This pairing code has expired.';
            return;
        }

        // Find the agent
        $agent = Agent::where('device_id', $pairingCodeRecord->device_id)->first();
        
        if (!$agent) {
            $this->pairingError = 'Agent not found for this pairing code.';
            return;
        }
        
        if ($agent->site_id) {
            $this->pairingError = 'This agent is already paired to a site.';
            return;
        }

        // Pair the agent
        $agent->update([
            'site_id' => $this->selectedSiteId,
            'status' => 'online',
            'paired_at' => now(),
        ]);

        // Mark pairing code as used
        $pairingCodeRecord->update([
            'used' => true,
            'used_at' => now(),
            'site_id' => $this->selectedSiteId,
            'paired_by' => Auth::id(),
        ]);

        $this->closePairingModal();
        $this->dispatch('agent-paired');
        
        session()->flash('success', 'Agent successfully paired to ' . $site->name);
    }

    public function viewAgent(Agent $agent)
    {
        // Verify the agent belongs to this company
        if ($agent->site->company_id !== $this->company->id) {
            abort(403);
        }
        
        $this->selectedAgent = $agent;
        $this->showAgentDetails = true;
    }

    public function closeAgentDetails()
    {
        $this->showAgentDetails = false;
        $this->selectedAgent = null;
    }

    public function refreshStatus()
    {
        // Update status of all agents
        $agents = Agent::whereHas('site', function ($q) {
            $q->where('company_id', $this->company->id);
        })->get();
        
        foreach ($agents as $agent) {
            $agent->updateStatus();
        }
        
        $this->dispatch('status-refreshed');
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->siteId = null;
        $this->resetPage();
    }
}