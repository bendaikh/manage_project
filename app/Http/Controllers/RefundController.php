<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_accounting');
        $this->middleware('permission:manage_refunds')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Refund::with(['expense', 'user']);

        // Apply filters
        if ($request->filled('expense')) {
            $query->where('expense_id', $request->expense);
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

        $refunds = $query->latest()->paginate(15);
        $expenses = Expense::where('refundable', true)->get();

        return view('accounting.refunds.index', compact('refunds', 'expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $expenses = Expense::where('refundable', true)->get();
        return view('accounting.refunds.create', compact('expenses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'expense_id' => 'required|exists:expenses,id',
            'description' => 'nullable|string',
        ]);

        // Check if the expense is refundable
        $expense = Expense::findOrFail($validated['expense_id']);
        if (!$expense->refundable) {
            return back()->withErrors(['expense_id' => 'This expense is not refundable.']);
        }

        $validated['user_id'] = Auth::id();

        Refund::create($validated);

        return redirect()->route('accounting.refunds.index')
            ->with('success', 'Refund created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Refund $refund)
    {
        return view('accounting.refunds.show', compact('refund'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Refund $refund)
    {
        $expenses = Expense::where('refundable', true)->get();
        return view('accounting.refunds.edit', compact('refund', 'expenses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Refund $refund)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'expense_id' => 'required|exists:expenses,id',
            'description' => 'nullable|string',
        ]);

        // Check if the expense is refundable
        $expense = Expense::findOrFail($validated['expense_id']);
        if (!$expense->refundable) {
            return back()->withErrors(['expense_id' => 'This expense is not refundable.']);
        }

        $refund->update($validated);

        return redirect()->route('accounting.refunds.index')
            ->with('success', 'Refund updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Refund $refund)
    {
        $refund->delete();

        return redirect()->route('accounting.refunds.index')
            ->with('success', 'Refund deleted successfully.');
    }
} 