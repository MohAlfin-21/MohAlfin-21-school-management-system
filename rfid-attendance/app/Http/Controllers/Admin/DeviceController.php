<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function index()
    {
        return view('admin.devices.index', [
            'devices' => Device::query()->orderBy('name')->paginate(20),
            'createdToken' => session('created_device_token'),
        ]);
    }

    public function create()
    {
        return view('admin.devices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $token = Str::random(40);

        Device::query()->create([
            'name' => $validated['name'],
            'location' => $validated['location'] ?? null,
            'token_hash' => hash('sha256', $token),
            'token_plain' => $token,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'ui.messages.device_created')
            ->with('created_device_token', $token);
    }

    public function edit(Device $device)
    {
        return view('admin.devices.edit', [
            'device' => $device,
        ]);
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $device->forceFill([
            'name' => $validated['name'],
            'location' => $validated['location'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ])->save();

        return redirect()->route('admin.devices.index')->with('status', 'ui.messages.device_updated');
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()->route('admin.devices.index')->with('status', 'ui.messages.device_deleted');
    }

    public function regenerateToken(Device $device)
    {
        $token = null;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = Str::random(40);
            $candidateHash = hash('sha256', $candidate);

            $exists = Device::query()->where('token_hash', $candidateHash)->exists();
            if (! $exists) {
                $token = $candidate;
                break;
            }
        }

        abort_if(! $token, 500, 'Could not generate device token.');

        $device->forceFill([
            'token_hash' => hash('sha256', $token),
            'token_plain' => $token,
        ])->save();

        return redirect()
            ->route('admin.devices.edit', $device)
            ->with('status', 'ui.messages.device_token_regenerated')
            ->with('created_device_token', $token);
    }
}
