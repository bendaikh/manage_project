<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Refund;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensesController extends Controller
{
    public function __construct()
    {
        // Middleware is handled by routes
    }

    /**
     * Display the expenses overview page
     */
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'account']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
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

        $expenses = $query->orderBy('date', 'desc')->paginate(15);
        $categories = ExpenseCategory::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json($expenses);
        }

        return view('accounting.expenses.index', compact('expenses', 'categories', 'accounts'));
    }

    /**
     * Show the form for creating a new expense
     */
    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        
        return view('accounting.expenses.create', compact('categories', 'accounts'));
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Map description to title for database
        $validated['title'] = $validated['description'];
        unset($validated['description']);
        
        $validated['user_id'] = Auth::id();

        $expense = Expense::create($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Expense created successfully.',
                'expense' => $expense->load(['category', 'account'])
            ], 201);
        }

        return redirect()->route('accounting.expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    /**
     * Show the form for editing the specified expense
     */
    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        
        return view('accounting.expenses.edit', compact('expense', 'categories', 'accounts'));
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Map description to title for database
        $validated['title'] = $validated['description'];
        unset($validated['description']);

        $expense->update($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Expense updated successfully.',
                'expense' => $expense->load(['category', 'account'])
            ]);
        }

        return redirect()->route('accounting.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Expense deleted successfully.'
            ]);
        }

        return redirect()->route('accounting.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Display expense categories management
     */
    public function categories()
    {
        $categories = ExpenseCategory::withCount('expenses')->orderBy('name')->paginate(15);
        
        if (request()->expectsJson()) {
            return response()->json($categories);
        }
        
        return view('accounting.expenses.categories', compact('categories'));
    }

    /**
     * Store a new expense category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories',
        ]);

        $category = ExpenseCategory::create($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Expense category created successfully.',
                'category' => $category
            ], 201);
        }

        return redirect()->route('accounting.expenses.categories')
            ->with('success', 'Expense category created successfully.');
    }

    /**
     * Update an expense category
     */
    public function updateCategory(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $category->id,
        ]);

        $category->update($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Expense category updated successfully.',
                'category' => $category
            ]);
        }

        return redirect()->route('accounting.expenses.categories')
            ->with('success', 'Expense category updated successfully.');
    }

    /**
     * Delete an expense category
     */
    public function destroyCategory(ExpenseCategory $category)
    {
        if ($category->expenses()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot delete category with existing expenses.'
                ], 422);
            }
            
            return redirect()->route('accounting.expenses.categories')
                ->with('error', 'Cannot delete category with existing expenses.');
        }

        $category->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Expense category deleted successfully.'
            ]);
        }

        return redirect()->route('accounting.expenses.categories')
            ->with('success', 'Expense category deleted successfully.');
    }

    /**
     * Display refunds management
     */
    public function refunds(Request $request)
    {
        $query = Refund::with(['expense', 'account']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
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

        $refunds = $query->orderBy('date', 'desc')->paginate(15);
        $accounts = Account::orderBy('name')->get();
        $expenses = Expense::orderBy('date', 'desc')->get();
        
        if (request()->expectsJson()) {
            return response()->json($refunds);
        }
        
        return view('accounting.expenses.refunds', compact('refunds', 'accounts', 'expenses'));
    }

    /**
     * Store a new refund
     */
    public function storeRefund(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'expense_id' => 'nullable|exists:expenses,id',
            'account_id' => 'required|exists:accounts,id',
            'order_sku' => 'nullable|string|max:255',
            'reason' => 'nullable|string',
        ]);

        // Map description to title for database
        $validated['title'] = $validated['description'];
        unset($validated['description']);
        
        $validated['user_id'] = Auth::id();

        $refund = Refund::create($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Refund created successfully.',
                'refund' => $refund->load(['expense', 'account'])
            ], 201);
        }

        return redirect()->route('accounting.expenses.refunds')
            ->with('success', 'Refund created successfully.');
    }

    /**
     * Update a refund
     */
    public function updateRefund(Request $request, Refund $refund)
    {
        $validated = $request->validate([
            'order_sku' => 'nullable|string|max:255',
            'reason' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $refund->update($validated);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Refund updated successfully.',
                'refund' => $refund->load(['expense', 'account'])
            ]);
        }

        return redirect()->route('accounting.expenses.refunds')
            ->with('success', 'Refund updated successfully.');
    }

    /**
     * Delete a refund
     */
    public function destroyRefund(Refund $refund)
    {
        $refund->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Refund deleted successfully.'
            ]);
        }

        return redirect()->route('accounting.expenses.refunds')
            ->with('success', 'Refund deleted successfully.');
    }
} 