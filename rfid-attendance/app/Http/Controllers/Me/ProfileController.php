<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        $profile = StudentProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['nisn' => $user->username]
        );

        return view('me.profile', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $profile = StudentProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['nisn' => $user->username]
        );

        $this->authorize('update', $profile);

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $profile->forceFill($validated)->save();

        return redirect()->route('me.profile.edit')->with('status', 'ui.messages.profile_updated');
    }
}
