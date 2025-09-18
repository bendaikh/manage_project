<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;

class MarketplaceController extends Controller
{
    /**
     * Display the marketplace for sellers to see their assigned products.
     */
    public function index(Request $request)
    {
        // Only allow sellers to access this
        if (!auth()->user()->hasRole('seller')) {
            abort(403, 'Access denied. This page is only for sellers.');
        }

        $query = Product::with(['seller', 'warehouse', 'assignedSellers'])
            ->where('is_company_product', true)
            ->whereHas('assignedSellers', function ($q) {
                $q->where('seller_id', auth()->id());
            });

        // Apply filters
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

        // Apply sorting
        switch ($request->input('sort')) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
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
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(20);

        // Get filter options
        $categories = Product::where('is_company_product', true)
            ->whereHas('assignedSellers', function ($q) {
                $q->where('seller_id', auth()->id());
            })
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        $warehouses = \App\Models\Warehouse::all();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'warehouses' => $warehouses,
            'filters' => [
                'category' => $request->category,
                'status' => $request->status,
                'warehouse_id' => $request->warehouse_id,
                'search' => $request->search,
                'sort' => $request->sort,
            ]
        ]);
    }

    /**
     * Get statistics for the seller's marketplace.
     */
    public function stats()
    {
        if (!auth()->user()->hasRole('seller')) {
            abort(403, 'Access denied. This page is only for sellers.');
        }

        $sellerId = auth()->id();

        $totalProducts = Product::where('is_company_product', true)
            ->whereHas('assignedSellers', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->count();

        $inStockProducts = Product::where('is_company_product', true)
            ->where('status', 'In Stock')
            ->whereHas('assignedSellers', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->count();

        $lowStockProducts = Product::where('is_company_product', true)
            ->where('stock_quantity', '<=', 10)
            ->whereHas('assignedSellers', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->count();

        $totalValue = Product::where('is_company_product', true)
            ->whereHas('assignedSellers', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->sum(\DB::raw('selling_price * stock_quantity'));

        return response()->json([
            'total_products' => $totalProducts,
            'in_stock_products' => $inStockProducts,
            'low_stock_products' => $lowStockProducts,
            'total_value' => $totalValue,
        ]);
    }
}