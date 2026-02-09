<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\RfidCard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RfidCardController extends Controller
{
    public function index()
    {
        return view('admin.rfid-cards.index', [
            'cards' => RfidCard::query()
                ->with('user:id,name,username')
                ->orderByDesc('id')
                ->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.rfid-cards.create', [
            'students' => User::role('student')
                ->select('users.id', 'users.name', 'users.username')
                ->orderBy('name')
                ->get(),
            'devices' => Device::query()
                ->select('id', 'name', 'location')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'uid' => RfidCard::normalizeUid((string) $request->input('uid')),
        ]);

        $validated = $request->validate([
            'uid' => ['required', 'string', 'max:255', Rule::unique(RfidCard::class, 'uid')],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'status' => ['nullable', 'string', Rule::in(['active', 'lost', 'inactive'])],
        ]);

        $student = User::query()->findOrFail($validated['user_id']);
        if (! $student->hasRole('student')) {
            return back()->withErrors(['user_id' => 'User must have student role.']);
        }

        RfidCard::query()->create([
            'uid' => $validated['uid'],
            'user_id' => $student->id,
            'status' => $validated['status'] ?? 'active',
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        return redirect()->route('admin.rfid-cards.index')->with('status', 'ui.messages.rfid_card_created');
    }

    public function edit(RfidCard $rfid_card)
    {
        return view('admin.rfid-cards.edit', [
            'card' => $rfid_card->load('user'),
            'students' => User::role('student')
                ->select('users.id', 'users.name', 'users.username')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, RfidCard $rfid_card)
    {
        $request->merge([
            'uid' => RfidCard::normalizeUid((string) $request->input('uid')),
        ]);

        $validated = $request->validate([
            'uid' => ['required', 'string', 'max:255', Rule::unique(RfidCard::class, 'uid')->ignore($rfid_card->id)],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'status' => ['required', 'string', Rule::in(['active', 'lost', 'inactive'])],
        ]);

        $student = User::query()->findOrFail($validated['user_id']);
        if (! $student->hasRole('student')) {
            return back()->withErrors(['user_id' => 'User must have student role.']);
        }

        $rfid_card->forceFill([
            'uid' => $validated['uid'],
            'user_id' => $student->id,
            'status' => $validated['status'],
        ])->save();

        return redirect()->route('admin.rfid-cards.index')->with('status', 'ui.messages.rfid_card_updated');
    }

    public function destroy(RfidCard $rfid_card)
    {
        $rfid_card->delete();

        return redirect()->route('admin.rfid-cards.index')->with('status', 'ui.messages.rfid_card_deleted');
    }
}
