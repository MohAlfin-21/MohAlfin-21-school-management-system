<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('roles');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $role = $request->query('role');
        if (in_array($role, ['admin', 'teacher', 'secretary', 'student'], true)) {
            $query->whereHas('roles', function ($builder) use ($role) {
                $builder->where('name', $role);
            });
        }

        $sort = $request->query('sort', 'username');
        $direction = $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'username', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'username';
        }

        $users = $query->orderBy($sort, $direction)->paginate(20)->appends($request->query());

        return view('admin.users.index', [
            'users' => $users,
            'filters' => [
                'q' => $search,
                'role' => $role,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'roleOptions' => ['admin', 'teacher', 'secretary', 'student'],
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class, 'username')],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(['admin', 'teacher', 'secretary', 'student'])],
        ]);

        $password = $validated['password'] ?: 'password';

        $user = User::query()->create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($password),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $roles = collect($validated['roles'])->unique()->values();
        if ($roles->contains('secretary') && ! $roles->contains('student')) {
            $roles->push('student');
        }

        $user->syncRoles($roles->all());

        if ($roles->contains('student')) {
            StudentProfile::query()->firstOrCreate(['user_id' => $user->id], ['nisn' => $user->username]);
        }

        if ($roles->contains('teacher')) {
            TeacherProfile::query()->firstOrCreate(['user_id' => $user->id], ['full_name_with_title' => $user->name]);
        }

        return redirect()->route('admin.users.index')->with('status', 'ui.messages.user_created');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class, 'username')->ignore($user->id)],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(['admin', 'teacher', 'secretary', 'student'])],
        ]);

        $user->forceFill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        if (! empty($validated['password'])) {
            $user->forceFill(['password' => Hash::make($validated['password'])]);
        }

        $user->save();

        $roles = collect($validated['roles'])->unique()->values();
        if ($roles->contains('secretary') && ! $roles->contains('student')) {
            $roles->push('student');
        }
        $user->syncRoles($roles->all());

        if ($roles->contains('student')) {
            StudentProfile::query()->firstOrCreate(['user_id' => $user->id], ['nisn' => $user->username]);
        }

        if ($roles->contains('teacher')) {
            TeacherProfile::query()->firstOrCreate(['user_id' => $user->id], ['full_name_with_title' => $user->name]);
        }

        return redirect()->route('admin.users.index')->with('status', 'ui.messages.user_updated');
    }

    public function destroy(User $user)
    {
        $user->forceFill(['is_active' => false])->save();

        return redirect()->route('admin.users.index')->with('status', 'ui.messages.user_deactivated');
    }
}
