<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Traits\LogsActionHistory;

class UserController extends Controller
{
    use LogsActionHistory;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // JSON response for Vue lists
        if ($request->expectsJson() || $request->wantsJson()) {
            $query = User::with('roles');
            if ($request->filled('role')) {
                $roleName = $request->role;
                $query->whereHas('roles', function ($q) use ($roleName) {
                    $q->where('name', $roleName);
                });
            }
            $users = $query->get()->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'email' => $u->email,
                    'roles' => $u->roles->pluck('name'),
                ];
            });
            return response()->json(['users' => $users]);
        }

        // existing HTML view logic
        $users = User::with('roles')->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // If JSON request (API/AJAX from Vue), handle differently
        if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
            $validated = $request->validate([
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
                'role' => ['required', 'string'],
                'address' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
            ]);
            $user = \App\Models\User::create([
                'name' => $validated['username'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            ]);
            // Assign role by name
            $roleName = strtolower($validated['role']);
            // Ensure role exists
            $roleModel = \App\Models\Role::firstOrCreate(['name' => $roleName]);
            $user->assignRole($roleModel);
            // Optionally store address/phone in user meta or custom fields if needed
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['required', 'array'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->roles()->sync($request->roles);

        $this->logAction('User Created', "Created user: {$user->name}", ['user_id' => $user->id, 'roles' => $request->roles]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'roles' => ['required', 'array'],
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
        }

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->roles()->sync($request->roles);

        $this->logAction('User Updated', "Updated user: {$user->name}", ['user_id' => $user->id, 'roles' => $request->roles]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Get all sellers for dropdown selection
     */
    public function getSellers()
    {
        $sellers = User::whereHas('roles', function ($query) {
            $query->where('name', 'seller');
        })->select('id', 'name', 'email')->orderBy('name')->get();
        
        return response()->json($sellers);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;
        $user->roles()->detach();
        $user->delete();

        $this->logAction('User Deleted', "Deleted user: {$userName}", ['user_id' => $user->id]);

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
} 