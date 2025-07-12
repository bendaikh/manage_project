<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_accounting');
        $this->middleware('permission:manage_transfers')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['fromAccount', 'toAccount', 'user']);

        // Apply filters
        if ($request->filled('from_account')) {
            $query->where('from_account_id', $request->from_account);
        }

        if ($request->filled('to_account')) {
            $query->where('to_account_id', $request->to_account);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        $transfers = $query->latest()->paginate(15);
        $accounts = Account::where('is_active', true)->get();

        return view('accounting.transfers.index', compact('transfers', 'accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = Account::where('is_active', true)->get();
        return view('accounting.transfers.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Check if from account has sufficient balance
        $fromAccount = Account::findOrFail($validated['from_account_id']);
        if ($fromAccount->balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient balance in the source account.']);
        }

        $validated['user_id'] = Auth::id();

        DB::transaction(function () use ($validated) {
            // Create the transfer
            Transfer::create($validated);

            // Update account balances
            $fromAccount = Account::find($validated['from_account_id']);
            $toAccount = Account::find($validated['to_account_id']);

            $fromAccount->decrement('balance', $validated['amount']);
            $toAccount->increment('balance', $validated['amount']);
        });

        return redirect()->route('accounting.transfers.index')
            ->with('success', 'Transfer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transfer $transfer)
    {
        return view('accounting.transfers.show', compact('transfer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transfer $transfer)
    {
        $accounts = Account::where('is_active', true)->get();
        return view('accounting.transfers.edit', compact('transfer', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transfer $transfer)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Check if from account has sufficient balance (considering the old transfer)
        $fromAccount = Account::findOrFail($validated['from_account_id']);
        $availableBalance = $fromAccount->balance + $transfer->amount; // Add back the old transfer amount
        if ($availableBalance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient balance in the source account.']);
        }

        DB::transaction(function () use ($validated, $transfer) {
            // Reverse the old transfer
            $oldFromAccount = Account::find($transfer->from_account_id);
            $oldToAccount = Account::find($transfer->to_account_id);

            $oldFromAccount->increment('balance', $transfer->amount);
            $oldToAccount->decrement('balance', $transfer->amount);

            // Update the transfer
            $transfer->update($validated);

            // Apply the new transfer
            $newFromAccount = Account::find($validated['from_account_id']);
            $newToAccount = Account::find($validated['to_account_id']);

            $newFromAccount->decrement('balance', $validated['amount']);
            $newToAccount->increment('balance', $validated['amount']);
        });

        return redirect()->route('accounting.transfers.index')
            ->with('success', 'Transfer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transfer $transfer)
    {
        DB::transaction(function () use ($transfer) {
            // Reverse the transfer
            $fromAccount = Account::find($transfer->from_account_id);
            $toAccount = Account::find($transfer->to_account_id);

            $fromAccount->increment('balance', $transfer->amount);
            $toAccount->decrement('balance', $transfer->amount);

            // Delete the transfer
            $transfer->delete();
        });

        return redirect()->route('accounting.transfers.index')
            ->with('success', 'Transfer deleted successfully.');
    }
} 