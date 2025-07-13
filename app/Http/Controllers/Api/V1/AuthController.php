<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Associate device with user if not already
        $device = Device::find($validated['device_id']);
        if ($device->user_id !== $user->id) {
            $device->user_id = $user->id;
            $device->save();
        }

        // Token name includes device id for traceability
        $token = $user->createToken('device-' . $device->id, [], now()->addYear());

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
            'device_id' => $device->id,
        ]);
    }
}
