<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomesController extends Controller
{
    public function __construct()
    {
        // Middleware is handled by routes
    }

    /**
     * Display the incomes overview page
     */
    public function index(Request $request)
    {
        $query = Income::with(['category', 'account']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('income_category_id', $request->category_id);
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
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

        $incomes = $query->orderBy('date', 'desc')->paginate(15);
        $categories = IncomeCategory::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json($incomes);
        }

        return view('accounting.incomes.index', compact('incomes', 'categories', 'accounts'));
    }

    /**
     * Show the form for creating a new income
     */
    public function create()
    {
        $categories = IncomeCategory::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        
        return view('accounting.incomes.create', compact('categories', 'accounts'));
    }

    /**
     * Store a newly created income
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'income_category_id' => 'required|exists:income_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Map description to title for database
        $validated['title'] = $validated['description'];
        unset($validated['description']);
        
        $validated['user_id'] = Auth::id();

        $income = Income::create($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Income created successfully.',
                'income' => $income->load(['category', 'account'])
            ], 201);
        }

        return redirect()->route('accounting.incomes.index')
            ->with('success', 'Income created successfully.');
    }

    /**
     * Show the form for editing the specified income
     */
    public function edit(Income $income)
    {
        $categories = IncomeCategory::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        
        return view('accounting.incomes.edit', compact('income', 'categories', 'accounts'));
    }

    /**
     * Update the specified income
     */
    public function update(Request $request, Income $income)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'income_category_id' => 'required|exists:income_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Map description to title for database
        $validated['title'] = $validated['description'];
        unset($validated['description']);

        $income->update($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Income updated successfully.',
                'income' => $income->load(['category', 'account'])
            ]);
        }

        return redirect()->route('accounting.incomes.index')
            ->with('success', 'Income updated successfully.');
    }

    /**
     * Remove the specified income
     */
    public function destroy(Income $income)
    {
        $income->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Income deleted successfully.'
            ]);
        }

        return redirect()->route('accounting.incomes.index')
            ->with('success', 'Income deleted successfully.');
    }

    /**
     * Display income categories management
     */
    public function categories()
    {
        $categories = IncomeCategory::withCount('incomes')->orderBy('name')->paginate(15);
        
        if (request()->expectsJson()) {
            return response()->json($categories);
        }
        
        return view('accounting.incomes.categories', compact('categories'));
    }

    /**
     * Store a new income category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_categories',
        ]);

        $category = IncomeCategory::create($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Income category created successfully.',
                'category' => $category
            ], 201);
        }

        return redirect()->route('accounting.incomes.categories')
            ->with('success', 'Income category created successfully.');
    }

    /**
     * Update an income category
     */
    public function updateCategory(Request $request, IncomeCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_categories,name,' . $category->id,
        ]);

        $category->update($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Income category updated successfully.',
                'category' => $category
            ]);
        }

        return redirect()->route('accounting.incomes.categories')
            ->with('success', 'Income category updated successfully.');
    }

    /**
     * Delete an income category
     */
    public function destroyCategory(IncomeCategory $category)
    {
        if ($category->incomes()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot delete category with existing incomes.'
                ], 422);
            }
            
            return redirect()->route('accounting.incomes.categories')
                ->with('error', 'Cannot delete category with existing incomes.');
        }

        $category->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Income category deleted successfully.'
            ]);
        }

        return redirect()->route('accounting.incomes.categories')
            ->with('success', 'Income category deleted successfully.');
    }
} 