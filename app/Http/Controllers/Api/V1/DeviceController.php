<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
        ]);

        $device = Device::create([
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
            'user_id' => null,
        ]);

        return response()->json([
            'device_id' => $device->id,
        ], 201);
    }
}
