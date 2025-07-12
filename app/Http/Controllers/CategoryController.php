<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('id')->get();
        return response()->json(['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);
        $category = Category::create(['name' => $validated['name']]);
        return response()->json(['category' => $category, 'message' => 'Category created successfully'], 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);
        
        $category->update(['name' => $validated['name']]);
        return response()->json(['category' => $category, 'message' => 'Category updated successfully']);
    }

    public function destroy(Category $category)
    {
        try {
            // Check if category is being used by any products
            $productCount = \App\Models\Product::where('category', $category->name)->count();
            if ($productCount > 0) {
                return response()->json([
                    'message' => "Cannot delete category '{$category->name}' because it is used by {$productCount} product(s)."
                ], 422);
            }
            
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete category'], 500);
        }
    }
} 