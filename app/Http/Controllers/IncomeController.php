<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_accounting');
        $this->middleware('permission:manage_incomes')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Income::with(['category', 'user']);

        // Apply filters
        if ($request->filled('category')) {
            $query->where('income_category_id', $request->category);
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

        $incomes = $query->latest()->paginate(15);
        $categories = IncomeCategory::all();

        return view('accounting.incomes.index', compact('incomes', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = IncomeCategory::all();
        return view('accounting.incomes.create', compact('categories'));
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
            'income_category_id' => 'required|exists:income_categories,id',
            'description' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        Income::create($validated);

        return redirect()->route('accounting.incomes.index')
            ->with('success', 'Income created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Income $income)
    {
        return view('accounting.incomes.show', compact('income'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Income $income)
    {
        $categories = IncomeCategory::all();
        return view('accounting.incomes.edit', compact('income', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Income $income)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'income_category_id' => 'required|exists:income_categories,id',
            'description' => 'nullable|string',
        ]);

        $income->update($validated);

        return redirect()->route('accounting.incomes.index')
            ->with('success', 'Income updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income)
    {
        $income->delete();

        return redirect()->route('accounting.incomes.index')
            ->with('success', 'Income deleted successfully.');
    }
} 