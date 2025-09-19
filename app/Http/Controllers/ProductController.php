<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActionHistory;
use App\Services\QuantitySyncService;

class ProductController extends Controller
{
    use LogsActionHistory;

    public function store(Request $request)
    {
        // Build validation rules. Only non-seller users must provide the seller field.
        $rules = [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'is_company_product' => 'boolean',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
            'image_url' => 'nullable|url|max:1024',
            'video_url' => 'nullable|url|max:1024',
            'video_duration' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'warehouse_stocks' => 'required|array|min:1',
            'warehouse_stocks.*.warehouse_id' => 'required|exists:warehouses,id',
            'warehouse_stocks.*.quantity' => 'required|integer|min:0',
        ];

        if (!auth()->user()->hasRole('seller')) {
            // If it's a company product, assigned_sellers is required instead of seller_id
            if ($request->input('is_company_product')) {
                $rules['assigned_sellers'] = 'required|array|min:1';
                $rules['assigned_sellers.*'] = 'exists:users,id';
            } else {
                $rules['seller_id'] = 'required|exists:users,id';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        // Force seller assignment for seller role users
        if (auth()->user()->hasRole('seller')) {
            $data['seller_id'] = auth()->user()->id;
            $data['seller'] = auth()->user()->name; // keep readable name
        } else {
            // For non-seller users, handle seller assignment based on product type
            if ($data['is_company_product']) {
                // For company products, we don't set a single seller_id
                // The assigned sellers will be handled via the pivot table
                $data['seller_id'] = null;
                $data['seller'] = null;
            } else {
                // For regular products, set the seller name for convenience
                if (isset($data['seller_id']) && empty($data['seller'])) {
                    $sellerUser = \App\Models\User::find($data['seller_id']);
                    $data['seller'] = $sellerUser?->name;
                }
            }
        }

        if (empty($data['sku'])) {
            $data['sku'] = strtoupper(uniqid('SKU'));
        }

        // Handle assigned sellers for company products
        $assignedSellers = $data['assigned_sellers'] ?? [];
        unset($data['assigned_sellers']); // Remove from data array as it's not a direct column

        // Handle warehouse stocks
        $warehouseStocks = $data['warehouse_stocks'] ?? [];
        unset($data['warehouse_stocks']); // Remove from data array as it's not a direct column

        $product = Product::create($data);

        // Attach assigned sellers if this is a company product
        if ($data['is_company_product'] && !empty($assignedSellers)) {
            $product->assignedSellers()->attach($assignedSellers);
        }

        // Attach warehouse stocks
        if (!empty($warehouseStocks)) {
            $warehouseData = [];
            foreach ($warehouseStocks as $warehouseStock) {
                $warehouseData[$warehouseStock['warehouse_id']] = ['quantity' => $warehouseStock['quantity']];
            }
            $product->warehouses()->attach($warehouseData);
            
            // Sync all quantities across the system
            QuantitySyncService::syncAllProductWarehouses($product->id);
        }

        $this->logAction('Product Created', "Created product: {$product->name}", ['product_id' => $product->id]);

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    public function index(Request $request)
    {
        $query = Product::with(['seller', 'warehouse', 'warehouses', 'assignedSellers']);

        // Sellers only see their own products
        if (auth()->check() && auth()->user()->hasRole('seller')) {
            $query->where('seller_id', auth()->user()->id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('warehouse_id')) {
            $query->whereHas('warehouses', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
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

        // Convert relative image URLs to full URLs for all products
        $products->transform(function ($product) {
            $product->image_url = $product->full_image_url;
            return $product;
        });

        // Categories should also respect seller constraint
        $categories = $query->clone()->select('category')->distinct()->pluck('category')->filter()->values();

        // Build summary counts based on the SAME filtered dataset so sellers see their own numbers
        $baseQuery = $query->clone();
        $summary = [
            'total' => $baseQuery->count(),
            'inStock' => (clone $baseQuery)->where('status', 'In Stock')->count(),
            'lowStock' => (clone $baseQuery)->where('status', 'Low Stock')->count(),
            'outOfStock' => (clone $baseQuery)->where('status', 'Out of Stock')->count(),
        ];

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'summary' => $summary,
        ]);
    }

    public function show(Product $product)
    {
        $product = $product->load(['seller', 'warehouse', 'warehouses', 'assignedSellers']);
        
        // Convert relative image URLs to full URLs
        $product->image_url = $product->full_image_url;
        
        return response()->json($product);
    }

    public function edit(Product $product)
    {
        $product = $product->load(['seller', 'warehouse', 'warehouses', 'assignedSellers']);
        
        // Convert relative image URLs to full URLs
        $product->image_url = $product->full_image_url;
        
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $updateRules = [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'is_company_product' => 'boolean',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
            'image_url' => 'nullable|url|max:1024',
            'video_url' => 'nullable|url|max:1024',
            'video_duration' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'warehouse_stocks' => 'required|array|min:1',
            'warehouse_stocks.*.warehouse_id' => 'required|exists:warehouses,id',
            'warehouse_stocks.*.quantity' => 'required|integer|min:0',
        ];

        if (!auth()->user()->hasRole('seller')) {
            // If it's a company product, assigned_sellers is required instead of seller_id
            if ($request->input('is_company_product', false)) {
                $updateRules['assigned_sellers'] = 'required|array|min:1';
                $updateRules['assigned_sellers.*'] = 'exists:users,id';
            } else {
                $updateRules['seller_id'] = 'required|exists:users,id';
            }
        }

        $validator = Validator::make($request->all(), $updateRules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        // Force seller assignment for seller role users
        if (auth()->user()->hasRole('seller')) {
            $data['seller_id'] = auth()->user()->id;
            $data['seller'] = auth()->user()->name;
        } else {
            // For non-seller users, handle seller assignment based on product type
            if (isset($data['is_company_product']) && $data['is_company_product']) {
                // For company products, we don't set a single seller_id
                // The assigned sellers will be handled via the pivot table
                $data['seller_id'] = null;
                $data['seller'] = null;
            } else {
                // For regular products, set the seller name for convenience
                if (isset($data['seller_id']) && empty($data['seller'])) {
                    $sellerUser = \App\Models\User::find($data['seller_id']);
                    $data['seller'] = $sellerUser?->name;
                }
            }
        }

        // Handle assigned sellers for company products
        $assignedSellers = $data['assigned_sellers'] ?? [];
        unset($data['assigned_sellers']); // Remove from data array as it's not a direct column

        // Handle warehouse stocks
        $warehouseStocks = $data['warehouse_stocks'] ?? [];
        unset($data['warehouse_stocks']); // Remove from data array as it's not a direct column

        $product->update($data);

        // Update assigned sellers if this is a company product
        if (isset($data['is_company_product']) && $data['is_company_product'] && !empty($assignedSellers)) {
            $product->assignedSellers()->sync($assignedSellers);
        }

        // Update warehouse stocks
        if (!empty($warehouseStocks)) {
            // Clear existing relationships and add new ones
            $product->warehouses()->detach();
            foreach ($warehouseStocks as $warehouseStock) {
                $product->warehouses()->attach($warehouseStock['warehouse_id'], ['quantity' => $warehouseStock['quantity']]);
            }
            
            // Sync all quantities across the system
            QuantitySyncService::syncAllProductWarehouses($product->id);
        }

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

    /**
     * Update stock quantity for a specific product
     */
    public function updateStockQuantity(Product $product)
    {
        try {
            $oldQuantity = $product->stock_quantity;
            $newQuantity = $product->updateStockQuantity();
            
            $this->logAction('Stock Quantity Updated', "Updated stock quantity for {$product->name}: {$oldQuantity} → {$newQuantity}", ['product_id' => $product->id]);
            
            return response()->json([
                'message' => 'Stock quantity updated successfully',
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update stock quantity'], 500);
        }
    }
} 