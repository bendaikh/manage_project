<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_roles');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        
        // If it's an API request, return JSON
        if (request()->expectsJson() || request()->isJson()) {
            // Add users count to each role
            $roles->each(function ($role) {
                $role->users_count = $role->users()->count();
            });
            return response()->json($roles);
        }
        
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        $role->permissions()->attach($validated['permissions']);

        // If it's an API request, return JSON
        if (request()->expectsJson() || request()->isJson()) {
            return response()->json(['message' => 'Role created successfully', 'role' => $role->load('permissions')], 201);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        $role->permissions()->sync($validated['permissions']);

        // If it's an API request, return JSON
        if (request()->expectsJson() || request()->isJson()) {
            return response()->json(['message' => 'Role updated successfully', 'role' => $role->load('permissions')]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'superadmin') {
            if (request()->expectsJson() || request()->isJson()) {
                return response()->json(['message' => 'Cannot delete the superadmin role.'], 422);
            }
            return back()->with('error', 'Cannot delete the superadmin role.');
        }

        $role->delete();

        if (request()->expectsJson() || request()->isJson()) {
            return response()->json(['message' => 'Role deleted successfully']);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
