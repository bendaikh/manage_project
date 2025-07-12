<?php

namespace App\Http\Controllers;

use App\Models\UserTransfer;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserTransferController extends Controller
{
    public function __construct()
    {
        // Remove middleware from constructor since it's causing issues
        // Middleware will be applied via routes instead
    }

    /**
     * Display a listing of user transfers
     */
    public function index(Request $request)
    {
        $query = UserTransfer::with(['fromUser.roles', 'toUser.roles']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('reason', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $transfers = $query->orderBy('date', 'desc')->paginate(15);

        if (request()->expectsJson()) {
            return response()->json($transfers);
        }

        return view('accounting.user-transfers.index', compact('transfers'));
    }

    /**
     * Store a newly created user transfer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id|different:from_user_id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $validated['user_id'] = Auth::id();

        $transfer = UserTransfer::create($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Transfer created successfully.',
                'transfer' => $transfer->load(['fromUser.roles', 'toUser.roles'])
            ], 201);
        }

        return redirect()->route('accounting.user-transfers.index')
            ->with('success', 'Transfer created successfully.');
    }

    /**
     * Update the specified user transfer
     */
    public function update(Request $request, UserTransfer $userTransfer)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id|different:from_user_id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $userTransfer->update($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Transfer updated successfully.',
                'transfer' => $userTransfer->load(['fromUser.roles', 'toUser.roles'])
            ]);
        }

        return redirect()->route('accounting.user-transfers.index')
            ->with('success', 'Transfer updated successfully.');
    }

    /**
     * Remove the specified user transfer
     */
    public function destroy(UserTransfer $userTransfer)
    {
        $userTransfer->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Transfer deleted successfully.'
            ]);
        }

        return redirect()->route('accounting.user-transfers.index')
            ->with('success', 'Transfer deleted successfully.');
    }

    /**
     * Get users by role type
     */
    public function getUsersByRole($roleName)
    {
        \Log::info("Getting users for role: " . $roleName);
        
        // First, let's get all roles to see what's available
        $allRoles = Role::all();
        \Log::info("Available roles: " . $allRoles->pluck('name')->join(', '));
        
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            \Log::info("Role not found: " . $roleName);
            return response()->json(['error' => 'Role not found', 'available_roles' => $allRoles->pluck('name')]);
        }

        \Log::info("Role found: " . $role->name . " (ID: " . $role->id . ")");

        $users = User::whereHas('roles', function ($query) use ($role) {
            $query->where('role_id', $role->id);
        })->get(['id', 'name', 'email']);

        \Log::info("Found " . $users->count() . " users for role " . $roleName);

        return response()->json($users);
    }
} 