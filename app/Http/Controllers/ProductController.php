<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActionHistory;

class ProductController extends Controller
{
    use LogsActionHistory;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|string|max:255',
            'image_url' => 'nullable|url|max:1024',
            'video_url' => 'nullable|url|max:1024',
            'video_duration' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
        if (empty($data['sku'])) {
            $data['sku'] = strtoupper(uniqid('SKU'));
        }

        $product = Product::create($data);

        $this->logAction('Product Created', "Created product: {$product->name}", ['product_id' => $product->id]);

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%")
                  ->orWhere('supplier', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        switch ($request->input('sort')) {
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('selling_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('selling_price', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $products = $query->get();
        $categories = Product::select('category')->distinct()->pluck('category')->filter()->values();
        $summary = [
            'total' => Product::count(),
            'inStock' => Product::where('status', 'In Stock')->count(),
            'lowStock' => Product::where('status', 'Low Stock')->count(),
            'outOfStock' => Product::where('status', 'Out of Stock')->count(),
        ];

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'summary' => $summary,
        ]);
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function edit(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|string|max:255',
            'image_url' => 'nullable|url|max:1024',
            'video_url' => 'nullable|url|max:1024',
            'video_duration' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
        $product->update($data);

        $this->logAction('Product Updated', "Updated product: {$product->name}", ['product_id' => $product->id]);

        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    public function destroy(Product $product)
    {
        $productName = $product->name;
        try {
            $product->delete();
            $this->logAction('Product Deleted', "Deleted product: {$productName}", ['product_id' => $product->id]);
            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product'], 500);
        }
    }
} 