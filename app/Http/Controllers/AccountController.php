<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct()
    {
        // Middleware is handled by routes
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Account::paginate(15);
        
        if (request()->expectsJson()) {
            return response()->json($accounts);
        }
        
        return view('accounting.accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accounts',
            'description' => 'nullable|string',
            'initial_balance' => 'required|numeric|min:0',
            'type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $validated['current_balance'] = $validated['initial_balance'];

        $account = Account::create($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Account created successfully.',
                'account' => $account
            ], 201);
        }

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        return view('accounting.accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        return view('accounting.accounts.edit', compact('account'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name,' . $account->id,
            'description' => 'nullable|string',
            'initial_balance' => 'required|numeric|min:0',
            'type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $account->update($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Account updated successfully.',
                'account' => $account
            ]);
        }

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        // Check if account has any transfers
        if ($account->transfersFrom()->count() > 0 || $account->transfersTo()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot delete account with existing transfers.'
                ], 422);
            }
            
            return redirect()->route('accounting.accounts.index')
                ->with('error', 'Cannot delete account with existing transfers.');
        }

        $account->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Account deleted successfully.'
            ]);
        }

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
} 