<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentHeartbeat;
use App\Models\PairingCode;
use App\Models\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    /**
     * Register a new agent or update existing
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'thing_name' => 'nullable|string',
            'type' => 'nullable|string',
            'firmware_version' => 'nullable|string',
            'ip_address' => 'nullable|string',
        ]);

        $agent = Agent::updateOrCreate(
            ['device_id' => $validated['device_id']],
            [
                'thing_name' => $validated['thing_name'] ?? null,
                'type' => $validated['type'] ?? 'raspberry-pi-5',
                'firmware_version' => $validated['firmware_version'] ?? null,
                'ip_address' => $validated['ip_address'] ?? $request->ip(),
                'status' => 'provisioning',
                'provisioned_at' => now(),
            ]
        );

        // Generate pairing code
        $pairingCode = PairingCode::generate($agent->device_id, $agent->thing_name);

        return response()->json([
            'success' => true,
            'agent_id' => $agent->id,
            'device_id' => $agent->device_id,
            'pairing_code' => $pairingCode->code,
            'expires_at' => $pairingCode->expires_at->toIso8601String(),
        ]);
    }

    /**
     * Request a new pairing code
     */
    public function requestPairingCode(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'thing_name' => 'nullable|string',
        ]);

        $agent = Agent::where('device_id', $validated['device_id'])->first();
        
        if (!$agent) {
            return response()->json([
                'success' => false,
                'error' => 'Agent not registered. Please register first.',
            ], 404);
        }

        // Check if already paired
        if ($agent->site_id && $agent->paired_at) {
            return response()->json([
                'success' => false,
                'error' => 'Agent is already paired to a site.',
                'site_id' => $agent->site_id,
            ], 400);
        }

        // Invalidate any existing unused codes
        PairingCode::where('device_id', $agent->device_id)
            ->where('used', false)
            ->update(['expires_at' => now()]);

        // Generate new pairing code
        $pairingCode = PairingCode::generate($agent->device_id, $agent->thing_name);

        return response()->json([
            'success' => true,
            'pairing_code' => $pairingCode->code,
            'expires_at' => $pairingCode->expires_at->toIso8601String(),
        ]);
    }

    /**
     * Validate and use a pairing code
     */
    public function validatePairingCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'site_id' => 'required|exists:sites,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $pairingCode = PairingCode::where('code', $validated['code'])->first();

        if (!$pairingCode) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid pairing code.',
            ], 404);
        }

        if (!$pairingCode->isValid()) {
            return response()->json([
                'success' => false,
                'error' => $pairingCode->used ? 'Pairing code has already been used.' : 'Pairing code has expired.',
            ], 400);
        }

        // Find the agent
        $agent = Agent::where('device_id', $pairingCode->device_id)->first();
        
        if (!$agent) {
            return response()->json([
                'success' => false,
                'error' => 'Agent not found for this pairing code.',
            ], 404);
        }

        DB::transaction(function () use ($agent, $pairingCode, $validated) {
            // Update agent
            $agent->update([
                'site_id' => $validated['site_id'],
                'status' => 'pairing',
                'paired_at' => now(),
            ]);

            // Mark pairing code as used
            $pairingCode->update([
                'used' => true,
                'used_at' => now(),
                'site_id' => $validated['site_id'],
                'paired_by' => $validated['user_id'],
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Agent successfully paired to site.',
            'agent_id' => $agent->id,
            'device_id' => $agent->device_id,
            'site_id' => $agent->site_id,
        ]);
    }

    /**
     * Receive heartbeat from agent
     */
    public function heartbeat(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'status' => 'nullable|string|in:healthy,warning,critical',
            'metrics' => 'nullable|array',
            'knx_status' => 'nullable|array',
            'uptime' => 'nullable|integer',
            'ip_address' => 'nullable|string',
        ]);

        $agent = Agent::where('device_id', $validated['device_id'])->first();
        
        if (!$agent) {
            return response()->json([
                'success' => false,
                'error' => 'Agent not registered.',
            ], 404);
        }

        // Create heartbeat record
        $heartbeat = AgentHeartbeat::create([
            'agent_id' => $agent->id,
            'status' => $validated['status'] ?? 'healthy',
            'metrics' => $validated['metrics'] ?? null,
            'knx_status' => $validated['knx_status'] ?? null,
            'uptime' => $validated['uptime'] ?? null,
            'ip_address' => $validated['ip_address'] ?? $request->ip(),
        ]);

        // Update agent status
        $agent->update([
            'status' => 'online',
            'last_heartbeat_at' => now(),
            'ip_address' => $validated['ip_address'] ?? $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'heartbeat_id' => $heartbeat->id,
            'agent_status' => $agent->status,
        ]);
    }

    /**
     * Receive telegrams from agent (placeholder for now)
     */
    public function telegrams(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'telegrams' => 'required|array',
        ]);

        $agent = Agent::where('device_id', $validated['device_id'])->first();
        
        if (!$agent) {
            return response()->json([
                'success' => false,
                'error' => 'Agent not registered.',
            ], 404);
        }

        // TODO: Implement telegram storage once we align with existing schema
        
        return response()->json([
            'success' => true,
            'count' => count($validated['telegrams']),
            'message' => 'Telegrams received (not stored yet - schema alignment needed)',
        ]);
    }

    /**
     * Get agent status
     */
    public function status($deviceId)
    {
        $agent = Agent::where('device_id', $deviceId)->first();
        
        if (!$agent) {
            return response()->json([
                'success' => false,
                'error' => 'Agent not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'agent' => [
                'device_id' => $agent->device_id,
                'thing_name' => $agent->thing_name,
                'status' => $agent->status,
                'site_id' => $agent->site_id,
                'paired_at' => $agent->paired_at?->toIso8601String(),
                'last_heartbeat_at' => $agent->last_heartbeat_at?->toIso8601String(),
                'is_online' => $agent->isOnline(),
            ],
        ]);
    }
}