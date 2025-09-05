<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class StockGlobaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::query()->with(['seller', 'shipment', 'product', 'warehouse']);
        
        // Global view - show all stocks regardless of seller (admin/manager view)
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        
        // Enhanced filtering
        $this->applyFilters($query, $request);
        
        $stocks = $query->orderByDesc('created_at')->paginate(15);
        
        // Calculate today's quantities for each stock
        $stocks->getCollection()->transform(function ($stock) {
            $todayQuantities = $this->calculateTodayQuantities($stock);
            $stock->today_delivered_quantity = $todayQuantities['delivered'];
            $stock->today_in_progress_quantity = $todayQuantities['in_progress'];
            return $stock;
        });
        
        return response()->json($stocks);
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, Request $request)
    {
        // Keyword search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by product
        if ($request->filled('product_id')) {
            $productId = $request->product_id;
            
            // Handle special case for "unlinked" stocks
            if ($productId === 'unlinked') {
                $query->whereNull('product_id');
            } else {
                $query->where(function($q) use ($productId) {
                    // First try to match by direct product_id link
                    $q->where('product_id', $productId)
                      // If no direct link, try to match by product name or reference
                      ->orWhere(function($subQ) use ($productId) {
                          $product = Product::find($productId);
                          if ($product) {
                              $subQ->where('title', 'like', "%{$product->name}%")
                                   ->orWhere('reference', 'like', "%{$product->sku}%")
                                   ->orWhere('reference', 'like', "%{$product->name}%");
                          }
                      });
                });
            }
        }
        
        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Filter by seller
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by warehouse location
        if ($request->filled('warehouse_location')) {
            $query->where('warehouse_location', 'like', "%{$request->warehouse_location}%");
        }
        
        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where(function($q) use ($request) {
                $q->where('purchase_price', '>=', $request->min_price)
                  ->orWhere('selling_price', '>=', $request->min_price);
            });
        }
        
        if ($request->filled('max_price')) {
            $query->where(function($q) use ($request) {
                $q->where('purchase_price', '<=', $request->max_price)
                  ->orWhere('selling_price', '<=', $request->max_price);
            });
        }
    }

    /**
     * Calculate today's delivered and in-progress quantities for a stock
     */
    private function calculateTodayQuantities($stock)
    {
        $today = now()->setTimezone('UTC')->toDateString();
        
        // Get the seller name from the stock's seller relationship
        $sellerName = $stock->seller ? $stock->seller->name : null;
        
        if (!$sellerName) {
            return [
                'delivered' => 0,
                'in_progress' => 0
            ];
        }
        
        // Build the query for orders
        $orderQuery = \App\Models\Order::where('seller', $sellerName);
        
        // If stock has a linked product, match by product_id
        if ($stock->product_id) {
            $orderQuery->where('product_id', $stock->product_id);
        } else {
            // If no linked product, try to match by product name or reference
            $matchingProducts = \App\Models\Product::where(function($q) use ($stock) {
                $q->where('name', 'like', "%{$stock->title}%")
                  ->orWhere('sku', 'like', "%{$stock->reference}%")
                  ->orWhere('name', 'like', "%{$stock->reference}%");
            })->pluck('id');
            
            if ($matchingProducts->count() > 0) {
                $orderQuery->whereIn('product_id', $matchingProducts);
            } else {
                return [
                    'delivered' => 0,
                    'in_progress' => 0
                ];
            }
        }
        
        // For "Delivered Today" - show orders that were delivered TODAY
        $todayDelivered = (clone $orderQuery)
            ->whereHas('orderStatus', function($q) {
                $q->where('name', 'Delivered');
            })
            ->whereDate('created_at', $today)
            ->sum('quantity');
            
        // For "In Progress Today" - show orders that were created TODAY and are currently in Shipped or Processing status
        $todayShipped = (clone $orderQuery)
            ->whereHas('orderStatus', function($q) {
                $q->whereIn('name', ['Shipped', 'Processing']);
            })
            ->whereDate('created_at', $today)
            ->sum('quantity');
            
        return [
            'delivered' => $todayDelivered,
            'in_progress' => $todayShipped
        ];
    }

    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        
        // Check permissions
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403);
        }
        
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'initial_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);
        
        $data['last_updated_by'] = Auth::user()->name;
        $data['last_updated_at'] = now();
        
        $stock->update($data);
        $stock->recalculateRemainingQuantity()->save();
        
        return response()->json(['message' => 'Stock updated successfully', 'stock' => $stock]);
    }

    /**
     * Update stock quantities (delivered, damaged, in progress)
     */
    public function updateQuantities(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        
        // Check permissions
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403);
        }
        
        $data = $request->validate([
            'delivered_quantity' => 'nullable|integer|min:0',
            'damaged_quantity' => 'nullable|integer|min:0',
            'in_progress_quantity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        
        $data['last_updated_by'] = Auth::user()->name;
        $data['last_updated_at'] = now();
        
        // Add update note
        $updateNote = "Quantities updated on " . now()->format('Y-m-d H:i:s') . " by " . Auth::user()->name;
        if ($request->filled('notes')) {
            $updateNote .= " - " . $request->notes;
        }
        $data['notes'] = $stock->notes ? $stock->notes . "\n" . $updateNote : $updateNote;
        
        $stock->update($data);
        $stock->recalculateRemainingQuantity()->save();
        
        return response()->json([
            'message' => 'Stock quantities updated successfully', 
            'stock' => $stock->fresh()
        ]);
    }

    /**
     * Get stock statistics for global view
     */
    public function statistics()
    {
        // Base query for seller filtering
        $baseQuery = Stock::query();
        
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $baseQuery->where('seller_id', Auth::id());
        }
        
        $stocks = $baseQuery->get();
        
        // Calculate today's totals
        $today = now()->setTimezone('UTC')->toDateString();
        $todayDeliveredTotal = 0;
        $todayInProgressTotal = 0;
        
        foreach ($stocks as $stock) {
            $todayQuantities = $this->calculateTodayQuantities($stock);
            $todayDeliveredTotal += $todayQuantities['delivered'];
            $todayInProgressTotal += $todayQuantities['in_progress'];
        }
        
        $stats = [
            'total_products' => $stocks->count(),
            'in_stock' => $stocks->where('status', 'in_stock')->count(),
            'low_stock' => $stocks->where('status', 'low_stock')->count(),
            'out_of_stock' => $stocks->where('status', 'out_of_stock')->count(),
            'total_initial_quantity' => $stocks->sum('initial_quantity'),
            'total_remaining_quantity' => $stocks->sum('remaining_quantity'),
            'total_delivered_quantity_today' => $todayDeliveredTotal,
            'total_in_progress_quantity_today' => $todayInProgressTotal,
            'total_damaged_quantity' => $stocks->sum('damaged_quantity'),
        ];
        
        return response()->json($stats);
    }

    /**
     * Get filter options for products
     */
    public function getFilterOptionsProducts()
    {
        $query = Product::query();
        
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        
        $products = $query->select('id', 'name', 'sku', 'category')
            ->orderBy('name')
            ->get();
        
        // Add a special option for "Unlinked Stocks" if there are stocks without product_id
        $unlinkedStocksCount = Stock::whereNull('product_id')->count();
        if ($unlinkedStocksCount > 0) {
            $products->prepend([
                'id' => 'unlinked',
                'name' => "Unlinked Stocks ({$unlinkedStocksCount} items)",
                'sku' => 'UNLINKED',
                'category' => 'System'
            ]);
        }
        
        return response()->json($products);
    }

    /**
     * Get filter options for warehouses
     */
    public function getFilterOptionsWarehouses()
    {
        $warehouses = Warehouse::select('id', 'name', 'location')
            ->orderBy('name')
            ->get();
        
        return response()->json($warehouses);
    }

    /**
     * Get filter options for sellers
     */
    public function getFilterOptionsSellers()
    {
        // Only show sellers if user is admin/manager
        if (Auth::user()->hasRole('seller')) {
            return response()->json([]);
        }
        
        $sellers = User::whereHas('roles', function($q) {
            $q->where('name', 'seller');
        })
        ->select('id', 'name', 'email')
        ->orderBy('name')
        ->get();
        
        return response()->json($sellers);
    }

    /**
     * Export stock data to CSV
     */
    public function export(Request $request)
    {
        $query = Stock::query()->with(['seller', 'product', 'warehouse']);
        
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        
        // Apply same filters as index
        $this->applyFilters($query, $request);
        
        $stocks = $query->orderByDesc('created_at')->get();
        
        $filename = 'stock-globale-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($stocks) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Product Title',
                'Reference',
                'Barcode',
                'Seller',
                'Warehouse',
                'Warehouse Location',
                'Initial Quantity',
                'Remaining Quantity',
                'Delivered Quantity',
                'Damaged Quantity',
                'In Progress Quantity',
                'Status',
                'Purchase Price',
                'Selling Price',
                'Last Updated By',
                'Last Updated At',
                'Created At'
            ]);
            
            // CSV data
            foreach ($stocks as $stock) {
                fputcsv($file, [
                    $stock->title,
                    $stock->reference,
                    $stock->barcode,
                    $stock->seller ? $stock->seller->name : 'N/A',
                    $stock->warehouse ? $stock->warehouse->name : 'N/A',
                    $stock->warehouse_location,
                    $stock->initial_quantity,
                    $stock->remaining_quantity,
                    $stock->delivered_quantity,
                    $stock->damaged_quantity,
                    $stock->in_progress_quantity,
                    $stock->status,
                    $stock->purchase_price,
                    $stock->selling_price,
                    $stock->last_updated_by,
                    $stock->last_updated_at,
                    $stock->created_at
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
}
