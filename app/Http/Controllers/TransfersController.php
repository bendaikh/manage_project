<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransfersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_transfers');
    }

    /**
     * Display the transfers page
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['fromAccount', 'toAccount']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('from_account_id')) {
            $query->where('from_account_id', $request->from_account_id);
        }

        if ($request->filled('to_account_id')) {
            $query->where('to_account_id', $request->to_account_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        $transfers = $query->orderBy('date', 'desc')->paginate(15);
        $accounts = Account::orderBy('name')->get();

        return view('accounting.transfers.index', compact('transfers', 'accounts'));
    }

    /**
     * Show the form for creating a new transfer
     */
    public function create()
    {
        $this->authorize('create', Transfer::class);
        
        $accounts = Account::orderBy('name')->get();
        
        return view('accounting.transfers.create', compact('accounts'));
    }

    /**
     * Store a newly created transfer
     */
    public function store(Request $request)
    {
        $this->authorize('create', Transfer::class);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        // Use transaction to ensure data consistency
        DB::transaction(function () use ($validated) {
            $transfer = Transfer::create($validated);
            
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
     * Show the form for editing the specified transfer
     */
    public function edit(Transfer $transfer)
    {
        $this->authorize('update', $transfer);
        
        $accounts = Account::orderBy('name')->get();
        
        return view('accounting.transfers.edit', compact('transfer', 'accounts'));
    }

    /**
     * Update the specified transfer
     */
    public function update(Request $request, Transfer $transfer)
    {
        $this->authorize('update', $transfer);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Use transaction to ensure data consistency
        DB::transaction(function () use ($transfer, $validated) {
            // Revert the old transfer
            $oldFromAccount = Account::find($transfer->from_account_id);
            $oldToAccount = Account::find($transfer->to_account_id);
            
            $oldFromAccount->increment('balance', $transfer->amount);
            $oldToAccount->decrement('balance', $transfer->amount);
            
            // Apply the new transfer
            $transfer->update($validated);
            
            $newFromAccount = Account::find($validated['from_account_id']);
            $newToAccount = Account::find($validated['to_account_id']);
            
            $newFromAccount->decrement('balance', $validated['amount']);
            $newToAccount->increment('balance', $validated['amount']);
        });

        return redirect()->route('accounting.transfers.index')
            ->with('success', 'Transfer updated successfully.');
    }

    /**
     * Remove the specified transfer
     */
    public function destroy(Transfer $transfer)
    {
        $this->authorize('delete', $transfer);

        // Use transaction to ensure data consistency
        DB::transaction(function () use ($transfer) {
            // Revert the transfer
            $fromAccount = Account::find($transfer->from_account_id);
            $toAccount = Account::find($transfer->to_account_id);
            
            $fromAccount->increment('balance', $transfer->amount);
            $toAccount->decrement('balance', $transfer->amount);
            
            $transfer->delete();
        });

        return redirect()->route('accounting.transfers.index')
            ->with('success', 'Transfer deleted successfully.');
    }
} 