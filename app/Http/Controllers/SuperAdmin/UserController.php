<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreUserRequest;
use App\Http\Requests\SuperAdmin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Users/Index', [
            'users' => User::query()
                ->with('roles')
                ->latest()
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('SuperAdmin/Users/Create', [
            'roles' => Role::query()->pluck('name'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'google_id' => null,
            'is_active' => $request->boolean('is_active'),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$request->string('role')->toString()]);

        return redirect()->route('superadmin.users.index');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('SuperAdmin/Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->pluck('name'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $payload = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'username' => $request->input('username'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->string('password')->toString();
        }

        $user->update($payload);
        $user->syncRoles([$request->string('role')->toString()]);

        return redirect()->route('superadmin.users.index');
    }
}
