<?php

namespace App\Http\Controllers;

use App\Models\IncomeCategory;
use Illuminate\Http\Request;

class IncomeCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_income_categories');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = IncomeCategory::withCount('incomes')->paginate(15);
        return view('accounting.income-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.income-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_categories',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        IncomeCategory::create($validated);

        return redirect()->route('accounting.income-categories.index')
            ->with('success', 'Income category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IncomeCategory $incomeCategory)
    {
        return view('accounting.income-categories.edit', compact('incomeCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncomeCategory $incomeCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_categories,name,' . $incomeCategory->id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $incomeCategory->update($validated);

        return redirect()->route('accounting.income-categories.index')
            ->with('success', 'Income category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomeCategory $incomeCategory)
    {
        if ($incomeCategory->incomes()->count() > 0) {
            return redirect()->route('accounting.income-categories.index')
                ->with('error', 'Cannot delete category with existing incomes.');
        }

        $incomeCategory->delete();

        return redirect()->route('accounting.income-categories.index')
            ->with('success', 'Income category deleted successfully.');
    }
} 