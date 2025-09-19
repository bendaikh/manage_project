<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Upsell;
use Illuminate\Http\Request;
use App\Services\QuantitySyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class StockGlobaleController extends Controller
{
    public function index(Request $request)
    {
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to view global stock.');
        }
        
        $query = Stock::query()->with(['seller', 'shipment', 'product', 'warehouse', 'upsells']);
        
        // Global view - show all stocks regardless of seller (admin/manager view)
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        
        // Enhanced filtering
        $this->applyFilters($query, $request);
        
        $stocks = $query->orderByDesc('created_at')->paginate(15);
        
        // Use actual stock quantities from the database and add warehouse distribution
        $stocks->getCollection()->transform(function ($stock) {
            $stock->total_delivered_quantity = $stock->delivered_quantity ?? 0;
            $stock->total_in_progress_quantity = $stock->in_progress_quantity ?? 0;
            $stock->total_damaged_quantity = $stock->damaged_quantity ?? 0;
            
            // Get all warehouses where this product exists
            $stock->warehouse_distribution = $this->getProductWarehouseDistribution($stock);
            
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
     * Get all warehouses where a product is located with quantities from warehouse_stock pivot table
     */
    private function getProductWarehouseDistribution($stock)
    {
        $warehouseDistribution = [];
        
        // If stock has a linked product, get all warehouse quantities for this product
        if ($stock->product_id) {
            // Get all stocks for this product and their warehouse quantities from pivot table
            $productStocks = Stock::where('product_id', $stock->product_id)
                ->with(['warehouses' => function($query) {
                    $query->select('warehouses.id', 'warehouses.name', 'warehouses.location')
                          ->withPivot('quantity');
                }])
                ->get();
            
            foreach ($productStocks as $productStock) {
                foreach ($productStock->warehouses as $warehouse) {
                    $warehouseId = $warehouse->id;
                    $warehouseName = $warehouse->name;
                    $pivotQuantity = $warehouse->pivot->quantity;
                    
                    if (!isset($warehouseDistribution[$warehouseId])) {
                        $warehouseDistribution[$warehouseId] = [
                            'warehouse_id' => $warehouseId,
                            'warehouse_name' => $warehouseName,
                            'warehouse_location' => $warehouse->location,
                            'total_quantity' => 0,
                            'remaining_quantity' => 0,
                            'stocks' => []
                        ];
                    }
                    
                    $warehouseDistribution[$warehouseId]['total_quantity'] += $pivotQuantity;
                    $warehouseDistribution[$warehouseId]['remaining_quantity'] += $pivotQuantity;
                    $warehouseDistribution[$warehouseId]['stocks'][] = [
                        'stock_id' => $productStock->id,
                        'reference' => $productStock->reference,
                        'initial_quantity' => $productStock->initial_quantity,
                        'remaining_quantity' => $pivotQuantity,
                        'status' => $productStock->status
                    ];
                }
            }
        } else {
            // For unlinked stocks, get warehouse quantities from pivot table
            $stockWithWarehouses = Stock::with(['warehouses' => function($query) {
                $query->select('warehouses.id', 'warehouses.name', 'warehouses.location')
                      ->withPivot('quantity');
            }])->find($stock->id);
            
            if ($stockWithWarehouses && $stockWithWarehouses->warehouses->count() > 0) {
                foreach ($stockWithWarehouses->warehouses as $warehouse) {
                    $warehouseDistribution[$warehouse->id] = [
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'warehouse_location' => $warehouse->location,
                        'total_quantity' => $warehouse->pivot->quantity,
                        'remaining_quantity' => $warehouse->pivot->quantity,
                        'stocks' => [[
                            'stock_id' => $stock->id,
                            'reference' => $stock->reference,
                            'initial_quantity' => $stock->initial_quantity,
                            'remaining_quantity' => $warehouse->pivot->quantity,
                            'status' => $stock->status
                        ]]
                    ];
                }
            } else if ($stock->warehouse) {
                // Fallback to direct warehouse relationship if no pivot data
                $warehouseDistribution[$stock->warehouse->id] = [
                    'warehouse_id' => $stock->warehouse->id,
                    'warehouse_name' => $stock->warehouse->name,
                    'warehouse_location' => $stock->warehouse_location,
                    'total_quantity' => $stock->initial_quantity,
                    'remaining_quantity' => $stock->remaining_quantity,
                    'stocks' => [[
                        'stock_id' => $stock->id,
                        'reference' => $stock->reference,
                        'initial_quantity' => $stock->initial_quantity,
                        'remaining_quantity' => $stock->remaining_quantity,
                        'status' => $stock->status
                    ]]
                ];
            }
        }
        
        // Filter out warehouses with 0 quantity
        $filteredDistribution = array_filter($warehouseDistribution, function($warehouse) {
            return $warehouse['total_quantity'] > 0;
        });
        
        return array_values($filteredDistribution);
    }


    public function update(Request $request, $id)
    {
        // Check if user has permission to manage stock global
        if (!Auth::user()->hasPermission('manage_stock_global')) {
            abort(403, 'You do not have permission to manage global stock.');
        }
        
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
        // Check if user has permission to manage stock global
        if (!Auth::user()->hasPermission('manage_stock_global')) {
            abort(403, 'You do not have permission to manage global stock.');
        }
        
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
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to view global stock statistics.');
        }
        
        // Base query for seller filtering
        $baseQuery = Stock::query();
        
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $baseQuery->where('seller_id', Auth::id());
        }
        
        $stocks = $baseQuery->get();
        
        // Calculate total quantities from stock records
        $stats = [
            'total_products' => $stocks->count(),
            'in_stock' => $stocks->where('status', 'in_stock')->count(),
            'low_stock' => $stocks->where('status', 'low_stock')->count(),
            'out_of_stock' => $stocks->where('status', 'out_of_stock')->count(),
            'total_initial_quantity' => $stocks->sum('initial_quantity'),
            'total_remaining_quantity' => $stocks->sum('remaining_quantity'),
            'total_delivered_quantity' => $stocks->sum('delivered_quantity'),
            'total_in_progress_quantity' => $stocks->sum('in_progress_quantity'),
            'total_damaged_quantity' => $stocks->sum('damaged_quantity'),
        ];
        
        return response()->json($stats);
    }

    /**
     * Get filter options for products
     */
    public function getFilterOptionsProducts()
    {
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to view global stock filter options.');
        }
        
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
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to view global stock filter options.');
        }
        
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
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to view global stock filter options.');
        }
        
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
     * Update warehouse quantity for a specific stock
     */
    public function updateWarehouseQuantity(Request $request, $id)
    {
        // Check if user has permission to manage stock global
        if (!Auth::user()->hasPermission('manage_stock_global')) {
            abort(403, 'You do not have permission to manage global stock.');
        }
        
        $stock = Stock::findOrFail($id);
        
        // Check permissions
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403);
        }
        
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        
        try {
            // Check if the stock has a many-to-many relationship with warehouses
            if ($stock->warehouses()->where('warehouse_id', $data['warehouse_id'])->exists()) {
                // Update the pivot table quantity
                $stock->warehouses()->updateExistingPivot($data['warehouse_id'], [
                    'quantity' => $data['quantity'],
                    'updated_at' => now()
                ]);
            } else {
                // If no pivot relationship exists, create one
                $stock->warehouses()->attach($data['warehouse_id'], [
                    'quantity' => $data['quantity'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Update stock notes if provided
            if ($request->filled('notes')) {
                $updateNote = "Warehouse quantity updated on " . now()->format('Y-m-d H:i:s') . " by " . Auth::user()->name;
                $updateNote .= " - " . $request->notes;
                $stock->notes = $stock->notes ? $stock->notes . "\n" . $updateNote : $updateNote;
                $stock->save();
            }
            
            // Sync all quantities across the system
            QuantitySyncService::syncAllStockWarehouses($stock->id);
            
            // Update last updated info
            $stock->last_updated_by = Auth::user()->name;
            $stock->last_updated_at = now();
            $stock->save();
            
            return response()->json([
                'message' => 'Warehouse quantity updated successfully',
                'stock' => $stock->fresh(['warehouses'])
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update warehouse quantity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create warehouse transfer
     */
    public function createTransfer(Request $request)
    {
        // Check if user has permission to manage stock global
        if (!Auth::user()->hasPermission('manage_stock_global')) {
            abort(403, 'You do not have permission to create transfers.');
        }
        
        $data = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'transfers' => 'required|array|min:1',
            'transfers.*.to_warehouse_id' => 'required|exists:warehouses,id',
            'transfers.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);
        
        $stock = Stock::findOrFail($data['stock_id']);
        
        // Check permissions
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403);
        }
        
        // Get principal warehouse quantity
        $principalWarehouse = Warehouse::findOrFail($data['from_warehouse_id']);
        $principalQuantity = $stock->warehouses()
            ->where('warehouse_id', $data['from_warehouse_id'])
            ->first();
        
        if (!$principalQuantity) {
            return response()->json([
                'message' => 'No stock found in the selected principal warehouse'
            ], 400);
        }
        
        $availableQuantity = $principalQuantity->pivot->quantity;
        
        // Validate total transfer quantity doesn't exceed available
        $totalTransferQuantity = array_sum(array_column($data['transfers'], 'quantity'));
        
        if ($totalTransferQuantity > $availableQuantity) {
            return response()->json([
                'message' => "Total transfer quantity ({$totalTransferQuantity}) exceeds available quantity ({$availableQuantity}) in principal warehouse"
            ], 400);
        }
        
        try {
            \DB::beginTransaction();
            
            $createdTransfers = [];
            
            foreach ($data['transfers'] as $transfer) {
                // Create transfer record
                $warehouseTransfer = \App\Models\WarehouseTransfer::create([
                    'from_warehouse_id' => $data['from_warehouse_id'],
                    'to_warehouse_id' => $transfer['to_warehouse_id'],
                    'stock_id' => $data['stock_id'],
                    'quantity' => $transfer['quantity'],
                    'transfer_date' => now(),
                    'status' => 'completed',
                    'notes' => $data['notes'] ?? '',
                    'user_id' => Auth::id(),
                ]);
                
                // Update warehouse quantities
                // Reduce from principal warehouse
                $stock->warehouses()->updateExistingPivot($data['from_warehouse_id'], [
                    'quantity' => $availableQuantity - $transfer['quantity'],
                    'updated_at' => now()
                ]);
                
                // Add to destination warehouse
                $destinationWarehouse = $stock->warehouses()
                    ->where('warehouse_id', $transfer['to_warehouse_id'])
                    ->first();
                
                if ($destinationWarehouse) {
                    // Update existing quantity
                    $stock->warehouses()->updateExistingPivot($transfer['to_warehouse_id'], [
                        'quantity' => $destinationWarehouse->pivot->quantity + $transfer['quantity'],
                        'updated_at' => now()
                    ]);
                } else {
                    // Create new warehouse relationship
                    $stock->warehouses()->attach($transfer['to_warehouse_id'], [
                        'quantity' => $transfer['quantity'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                $createdTransfers[] = $warehouseTransfer;
                
                // Update available quantity for next iteration
                $availableQuantity -= $transfer['quantity'];
            }
            
            // Update stock notes
            $transferNote = "Transfer created on " . now()->format('Y-m-d H:i:s') . " by " . Auth::user()->name;
            if ($data['notes']) {
                $transferNote .= " - " . $data['notes'];
            }
            $stock->notes = $stock->notes ? $stock->notes . "\n" . $transferNote : $transferNote;
            $stock->last_updated_by = Auth::user()->name;
            $stock->last_updated_at = now();
            $stock->save();
            
            \DB::commit();
            
            return response()->json([
                'message' => 'Transfer created successfully',
                'transfers' => $createdTransfers,
                'stock' => $stock->fresh(['warehouses'])
            ]);
            
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json([
                'message' => 'Failed to create transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available stocks for transfer
     */
    public function getAvailableStocks()
    {
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to view stocks.');
        }
        
        $query = Stock::query()->with(['warehouses', 'product']);
        
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        
        $stocks = $query->whereHas('warehouses', function($q) {
            $q->where('warehouse_stock.quantity', '>', 0);
        })->get();
        
        // Transform to include warehouse distribution
        $stocks->transform(function ($stock) {
            $stock->warehouse_distribution = $this->getProductWarehouseDistribution($stock);
            return $stock;
        });
        
        return response()->json($stocks);
    }

    /**
     * Get principal warehouse for a stock
     */
    public function getPrincipalWarehouse($stockId)
    {
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to view stock details.');
        }
        
        $stock = Stock::with(['warehouses' => function($query) {
            $query->where('is_principal', true);
        }])->findOrFail($stockId);
        
        $principalWarehouse = $stock->warehouses->first();
        
        if (!$principalWarehouse) {
            return response()->json([
                'message' => 'No principal warehouse found for this stock'
            ], 404);
        }
        
        return response()->json([
            'warehouse' => $principalWarehouse,
            'quantity' => $principalWarehouse->pivot->quantity
        ]);
    }

    /**
     * Export stock data to CSV
     */
    public function export(Request $request)
    {
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to export global stock.');
        }
        
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

    /**
     * Get upsells for a specific stock
     */
    public function getUpsells($stockId)
    {
        // Check if user has permission to view stock global
        if (!Auth::user()->hasPermission('view_stock_global')) {
            abort(403, 'You do not have permission to view global stock.');
        }

        $stock = Stock::findOrFail($stockId);
        
        // Check if user can access this stock
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403, 'You do not have permission to view this stock.');
        }

        $upsells = $stock->upsells()->get();
        
        return response()->json($upsells);
    }

    /**
     * Store a new upsell for a stock
     */
    public function storeUpsell(Request $request, $stockId)
    {
        // Check if user has permission to manage stock
        if (!Auth::user()->hasPermission('manage_stock')) {
            abort(403, 'You do not have permission to manage stock.');
        }

        $stock = Stock::findOrFail($stockId);
        
        // Check if user can access this stock
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this stock.');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $upsell = $stock->upsells()->create([
            'name' => $request->name,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'is_active' => $request->get('is_active', true),
            'sort_order' => $request->get('sort_order', 0)
        ]);

        return response()->json([
            'message' => 'Upsell created successfully',
            'upsell' => $upsell
        ], 201);
    }

    /**
     * Update an existing upsell
     */
    public function updateUpsell(Request $request, $stockId, $upsellId)
    {
        // Check if user has permission to manage stock
        if (!Auth::user()->hasPermission('manage_stock')) {
            abort(403, 'You do not have permission to manage stock.');
        }

        $stock = Stock::findOrFail($stockId);
        
        // Check if user can access this stock
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this stock.');
        }

        $upsell = $stock->upsells()->findOrFail($upsellId);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $upsell->update([
            'name' => $request->name,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'is_active' => $request->get('is_active', true),
            'sort_order' => $request->get('sort_order', 0)
        ]);

        return response()->json([
            'message' => 'Upsell updated successfully',
            'upsell' => $upsell
        ]);
    }

    /**
     * Delete an upsell
     */
    public function deleteUpsell($stockId, $upsellId)
    {
        // Check if user has permission to manage stock
        if (!Auth::user()->hasPermission('manage_stock')) {
            abort(403, 'You do not have permission to manage stock.');
        }

        $stock = Stock::findOrFail($stockId);
        
        // Check if user can access this stock
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this stock.');
        }

        $upsell = $stock->upsells()->findOrFail($upsellId);
        $upsell->delete();

        return response()->json([
            'message' => 'Upsell deleted successfully'
        ]);
    }

    /**
     * Store multiple upsells for a stock
     */
    public function storeMultipleUpsells(Request $request, $stockId)
    {
        // Check if user has permission to manage stock
        if (!Auth::user()->hasPermission('manage_stock')) {
            abort(403, 'You do not have permission to manage stock.');
        }

        $stock = Stock::findOrFail($stockId);
        
        // Check if user can access this stock
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this stock.');
        }

        $request->validate([
            'upsells' => 'required|array|min:1',
            'upsells.*.quantity' => 'required|integer|min:1',
            'upsells.*.price' => 'required|numeric|min:0',
        ]);

        $createdUpsells = [];
        foreach ($request->upsells as $index => $upsellData) {
            $upsell = $stock->upsells()->create([
                'name' => null,
                'description' => null,
                'quantity' => $upsellData['quantity'],
                'price' => $upsellData['price'],
                'is_active' => true,
                'sort_order' => $index
            ]);
            $createdUpsells[] = $upsell;
        }

        return response()->json([
            'message' => 'Upsells created successfully',
            'upsells' => $createdUpsells
        ], 201);
    }
}
