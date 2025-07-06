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
} 