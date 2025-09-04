<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Traits\LogsActionHistory;
use Carbon\Carbon;

class OrderController extends Controller
{
    use LogsActionHistory;

    /**
     * Apply date range filter to the query
     */
    private function applyDateRangeFilter($query, $dateRange, $request = null)
    {
        switch ($dateRange) {
            case 'Today':
                // Special logic for "See Today Work" buttons
                if ($request && $request->filled('belongs_to') && $request->filled('status')) {
                    $belongsTo = $request->belongs_to;
                    $statuses = explode(',', $request->status);
                    
                    if ($belongsTo === 'delivery' && in_array('Postponed', $statuses)) {
                        // For delivery section with postponed status, only show orders scheduled for delivery today
                        $query->whereDate('postponed_date', Carbon::today());
                    } elseif ($belongsTo === 'confirmation') {
                        // For confirmation section, show orders scheduled for delivery today
                        $query->where(function($q) {
                            // Orders scheduled for delivery today (confirmed_date = today OR postponed_date = today)
                            $q->whereDate('confirmed_date', Carbon::today())
                              ->orWhereDate('postponed_date', Carbon::today());
                        });
                    } else {
                        // Default behavior for other cases
                        $query->where(function($q) {
                            $q->whereDate('created_at', Carbon::today())
                              ->orWhereDate('confirmed_date', Carbon::today())
                              ->orWhereDate('postponed_date', Carbon::today());
                        });
                    }
                } else {
                    // Default behavior when no specific context
                    $query->where(function($q) {
                        $q->whereDate('created_at', Carbon::today())
                          ->orWhereDate('confirmed_date', Carbon::today())
                          ->orWhereDate('postponed_date', Carbon::today());
                    });
                }
                break;
            case 'Yesterday':
                $query->whereDate('created_at', Carbon::yesterday());
                break;
            case 'This Month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'Last Month':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                      ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;
            // Add more date ranges as needed
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Build validation rules dynamically. If the authenticated user has the "seller"
        // role they should not (and cannot) supply the seller field – it will be filled
        // automatically with their own name. All other roles must supply it.

        $rules = [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'client_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'client_address' => 'required|string|max:255',
            'zone' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'order_status_id' => 'nullable|exists:order_statuses,id',
            'belongs_to' => 'nullable|in:confirmation,delivery',
        ];

        if (!auth()->user()->hasRole('seller')) {
            // Non-seller users (admin, manager, etc.) must choose a seller.
            $rules['seller'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        // Force seller value for authenticated sellers so they cannot spoof another seller.
        if (auth()->user()->hasRole('seller')) {
            $data['seller'] = auth()->user()->name;
        }

        if (empty($data['order_status_id'])) {
            $defaultStatus = \App\Models\OrderStatus::where('name', 'New Order')->first();
            $data['order_status_id'] = $defaultStatus ? $defaultStatus->id : null;
        }

        // Set belongs_to to 'confirmation' by default for new orders
        if (empty($data['belongs_to'])) {
            $data['belongs_to'] = 'confirmation';
        }

        $order = Order::create($data);

        // Log action
        $this->logAction('Order Created', 'Order #' . $order->id . ' created', ['order_id' => $order->id]);

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    public function index(Request $request)
    {
        $query = Order::with(['product', 'orderStatus', 'assignment.assignedTo', 'assignment.assignedBy', 'warehouse']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $searchLike = "%{$search}%";

                // General text search on columns that exist in the 'orders' table.
                $q->where('orders.client_name', 'like', $searchLike)
                  ->orWhere('orders.client_address', 'like', $searchLike)
                  ->orWhere('orders.seller', 'like', $searchLike)
                  ->orWhere('orders.zone', 'like', $searchLike)
                  ->orWhere('orders.comment', 'like', $searchLike);

                // Search in the related 'products' table.
                $q->orWhereHas('product', function ($pq) use ($searchLike) {
                    $pq->where('name', 'like', $searchLike)
                       ->orWhere('sku', 'like', $searchLike);
                });

                // Correctly search for the agent's name in the 'order_assignments' table through its relationship.
                $q->orWhereHas('assignment.assignedTo', function ($agentQuery) use ($searchLike) {
                    $agentQuery->where('name', 'like', $searchLike);
                });

                // Special handling for numeric-only search to include order ID.
                if (is_numeric($search)) {
                    $q->orWhere('orders.id', '=', $search);
                }

                // Special handling for phone numbers to match regardless of formatting.
                $q->orWhere('orders.client_phone', 'like', $searchLike);
                $digitsOnly = preg_replace('/[^0-9]/', '', $search);
                if (!empty($digitsOnly)) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(orders.client_phone, ' ', ''), '-', ''), '+', ''), '(', '') LIKE ?", ["%{$digitsOnly}%"]);
                }
            });
        }

        if ($request->filled('seller')) {
            $query->where('seller', $request->seller);
        }

        if ($request->filled('agent')) {
            $query->where('agent', $request->agent);
        }

        if ($request->filled('zone')) {
            $query->where('zone', $request->zone);
        }

        // Date range filtering
        if ($request->filled('dateRange')) {
            $this->applyDateRangeFilter($query, $request->dateRange, $request);
        }

        // Status filtering for specific pages
        if ($request->filled('status')) {
            $statuses = explode(',', $request->status);
            $query->whereHas('orderStatus', function($q) use ($statuses) {
                $q->whereIn('name', $statuses);
            });
        }

        if ($request->filled('exclude_status')) {
            $excludeStatuses = explode(',', $request->exclude_status);
            $query->whereHas('orderStatus', function($q) use ($excludeStatuses) {
                $q->whereNotIn('name', $excludeStatuses);
            });
        }

        // Check if user is a seller and should only see their own orders
        if (auth()->user()->hasRole('seller')) {
            $query->where('seller', auth()->user()->name);
        }

        // Check if user is an agent and show only assigned orders
        if (auth()->user()->isAgent()) {
            $query->whereHas('assignment', function($q) {
                $q->where('assigned_to', auth()->id());
            });
        }

        // Filter by assignment status
        if ($request->filled('assigned_only')) {
            // Show only orders that are assigned to agents (regardless of status)
            $query->whereHas('assignment');
        } elseif ($request->filled('unassigned_only')) {
            // Show only orders that are NOT assigned to agents
            $query->whereDoesntHave('assignment');
        }

        // Filter by belongs_to field (confirmation/delivery sections)
        if ($request->filled('belongs_to')) {
            $query->where('belongs_to', $request->belongs_to);
        }

        // Build query with filters (already built above $query)

        // Capture counts BEFORE pagination so they reflect the entire filtered dataset
        $countsQuery = clone $query;
        
        // For status blocks, we want to show today's orders only
        // Create a fresh query for status counts to avoid ambiguity issues
        $statusCountsQuery = Order::with(['orderStatus']);
        
        // Apply the same intelligent date filtering logic
        if ($request->filled('dateRange') && $request->dateRange === 'Today') {
            if ($request->filled('belongs_to') && $request->filled('status')) {
                $belongsTo = $request->belongs_to;
                $statuses = explode(',', $request->status);
                
                if ($belongsTo === 'delivery' && in_array('Postponed', $statuses)) {
                    // For delivery section with postponed status, only show orders scheduled for delivery today
                    $statusCountsQuery->whereDate('orders.postponed_date', Carbon::today());
                } elseif ($belongsTo === 'confirmation') {
                    // For confirmation section, show orders scheduled for delivery today
                    $statusCountsQuery->where(function($q) {
                        // Orders scheduled for delivery today (confirmed_date = today OR postponed_date = today)
                        $q->whereDate('orders.confirmed_date', Carbon::today())
                          ->orWhereDate('orders.postponed_date', Carbon::today());
                    });
                } else {
                    // Default behavior for other cases
                    $statusCountsQuery->where(function($q) {
                        $q->whereDate('orders.created_at', Carbon::today())
                          ->orWhereDate('orders.confirmed_date', Carbon::today())
                          ->orWhereDate('orders.postponed_date', Carbon::today());
                    });
                }
            } else {
                // Default behavior when no specific context
                $statusCountsQuery->where(function($q) {
                    $q->whereDate('orders.created_at', Carbon::today())
                      ->orWhereDate('orders.confirmed_date', Carbon::today())
                      ->orWhereDate('orders.postponed_date', Carbon::today());
                });
            }
        }
        
        // Apply the same filters as the main query for consistency
        if ($request->filled('belongs_to')) {
            $statusCountsQuery->where('belongs_to', $request->belongs_to);
        }
        
        // Apply user role filters
        if (auth()->user()->hasRole('seller')) {
            $statusCountsQuery->where('seller', auth()->user()->name);
        }
        
        if (auth()->user()->isAgent()) {
            $statusCountsQuery->whereHas('assignment', function($q) {
                $q->where('assigned_to', auth()->id());
            });
        }
        
        // Apply assignment filters
        if ($request->filled('assigned_only')) {
            $statusCountsQuery->whereHas('assignment');
        } elseif ($request->filled('unassigned_only')) {
            $statusCountsQuery->whereDoesntHave('assignment');
        }
        
        $statusCounts = $statusCountsQuery
            ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
            ->selectRaw('order_statuses.name as status_name, COUNT(*) as total')
            ->groupBy('order_statuses.name')
            ->pluck('total', 'status_name');

        // Apply pagination (default 10 items per page)
        $perPage = $request->input('per_page', 10);
        $orders = $query->orderBy('orders.created_at', 'desc')->paginate($perPage);

        // Get unique values for filters
        $sellers = Order::distinct()->pluck('seller')->filter()->values();
        $zones = Order::distinct()->pluck('zone')->filter()->values();

        // Count all orders delivered today across the whole dataset (ignoring pagination)
        $deliveredOrdersTodayCount = Order::whereHas('orderStatus', function ($q) {
            $q->where('name', 'Delivered');
        })->whereDate('updated_at', Carbon::today())->count();

        return response()->json([
            'orders' => $orders,
            'sellers' => $sellers,
            'zones' => $zones,
            'delivered_orders_today_count' => $deliveredOrdersTodayCount,
            'status_counts' => $statusCounts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = \App\Models\Order::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'seller' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'client_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'client_address' => 'required|string|max:255',
            'zone' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'agent' => 'nullable|string|max:255',
            'order_status_id' => 'nullable|exists:order_statuses,id',
            'belongs_to' => 'nullable|in:confirmation,delivery',
            'confirmed_date' => 'nullable|date',
            'confirmation_comment' => 'nullable|string',
            'postponed_date' => 'nullable|date',
            'postponed_comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        // If order_status_id not provided but status string is, resolve it
        if (empty($data['order_status_id']) && $request->filled('status')) {
            $statusModel = \App\Models\OrderStatus::where('name', $request->status)->first();
            if ($statusModel) {
                $data['order_status_id'] = $statusModel->id;
            }
        }

        // Fallback to current status if still empty
        if (empty($data['order_status_id'])) {
            $data['order_status_id'] = $order->order_status_id;
        }

        // Apply status conversion logic and update belongs_to accordingly
        if (!empty($data['order_status_id'])) {
            $statusModel = \App\Models\OrderStatus::find($data['order_status_id']);
            if ($statusModel) {
                // Handle confirmation and postponed data based on status
                if (strtolower($statusModel->name) === 'confirmed on date') {
                    $data['confirmed_date'] = $request->confirmed_date;
                    $data['confirmation_comment'] = $request->confirmation_comment;
                } elseif (strtolower($statusModel->name) === 'postponed') {
                    $data['postponed_date'] = $request->postponed_date;
                    $data['postponed_comment'] = $request->postponed_comment;
                }
                
                // Special case: When confirming an order, always move to delivery
                if (strtolower($statusModel->name) === 'confirmed') {
                    $data['belongs_to'] = 'delivery';
                } else {
                    // For all other status changes, set belongs_to based on where the change is coming from
                    if ($request->filled('source')) {
                        $data['belongs_to'] = $request->source;
                    }
                }
                
                // Special case: Convert "Confirmed" to "Processing" for delivery section
                if (strtolower($statusModel->name) === 'confirmed') {
                    $processingStatus = \App\Models\OrderStatus::where('name', 'Processing')->first();
                    if ($processingStatus) {
                        $data['order_status_id'] = $processingStatus->id;
                        $data['belongs_to'] = 'delivery';
                    }
                }
            }
        }

        $oldStatus = $order->status; // Get the old status before updating
        $order->update($data);
        
        // Update stock quantities if status changed
        if (!empty($data['order_status_id'])) {
            $statusModel = \App\Models\OrderStatus::find($data['order_status_id']);
            if ($statusModel && $oldStatus !== $statusModel->name) {
                $this->updateStockFromOrderStatus($order, $oldStatus, $statusModel->name);
            }
        }
        
        // Log action
        $this->logAction('Order Updated', 'Order #' . $order->id . ' updated', ['order_id' => $order->id]);
        
        // Return appropriate message based on status conversion
        $message = 'Order updated successfully';
        if (!empty($data['order_status_id'])) {
            $statusModel = \App\Models\OrderStatus::find($data['order_status_id']);
            if ($statusModel && strtolower($statusModel->name) === 'processing') {
                $message = 'Order confirmed and moved to Processing (Delivery)';
            }
        }
        
        return response()->json(['message' => $message, 'order' => $order], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $request->validate([ 
            'status' => 'required|string',
            'source' => 'nullable|in:confirmation,delivery', // Add source parameter
            'warehouse_id' => 'nullable|exists:warehouses,id', // Add warehouse_id parameter
            'confirmed_date' => 'nullable|date',
            'confirmation_comment' => 'nullable|string',
            'postponed_date' => 'nullable|date',
            'postponed_comment' => 'nullable|string'
        ]);
        
        $oldStatus = $order->status; // Get the old status before updating
        $statusName = $request->status;
        
        // If status is 'Confirmed', validate warehouse stock before proceeding
        if (strtolower($statusName) === 'confirmed') {
            if (!$request->filled('warehouse_id')) {
                return response()->json(['message' => 'Warehouse selection is required for order confirmation'], 422);
            }
            
            // Validate warehouse stock availability
            $stockValidation = $this->validateWarehouseStock($order, $request->warehouse_id);
            if (!$stockValidation['valid']) {
                return response()->json(['message' => $stockValidation['message']], 422);
            }
            
            $statusName = 'Processing';
        }
        
        // Find the status model
        $statusModel = \App\Models\OrderStatus::where('name', $statusName)->first();
        if (!$statusModel) {
            return response()->json(['message' => 'Invalid status'], 422);
        }
        
        $order->order_status_id = $statusModel->id;
        
        // Handle confirmation and postponed data based on status
        if (strtolower($request->status) === 'confirmed on date') {
            $order->confirmed_date = $request->confirmed_date;
            $order->confirmation_comment = $request->confirmation_comment;
        } elseif (strtolower($request->status) === 'postponed') {
            $order->postponed_date = $request->postponed_date;
            $order->postponed_comment = $request->postponed_comment;
        }
        
        // Special case: When confirming an order, always move to delivery
        if (strtolower($request->status) === 'confirmed') {
            $order->belongs_to = 'delivery';
            // Save warehouse_id when confirming order
            if ($request->filled('warehouse_id')) {
                $order->warehouse_id = $request->warehouse_id;
            }
        } else {
            // For all other status changes, set belongs_to based on where the change is coming from
            if ($request->filled('source')) {
                $order->belongs_to = $request->source;
            }
        }
        
        $order->save();
        
        // Update stock quantities based on status change
        $this->updateStockFromOrderStatus($order, $oldStatus, $statusName, $request->warehouse_id);
        
        // Log status change action
        $this->logAction('Order Status Updated', 'Order #' . $order->id . ' status changed to ' . $statusName, [
            'order_id' => $order->id,
            'new_status' => $statusName,
        ]);
        return response()->json([
            'message' => $request->status === 'Confirmed' ? 'Order confirmed and moved to Processing (Delivery)' : 'Status updated',
            'order' => $order->load('warehouse')
        ]);
    }

    /**
     * Update stock quantities based on order status changes
     */
    private function updateStockFromOrderStatus($order, $oldStatus, $newStatus, $warehouseId = null)
    {
        // Get the product from the order
        $product = $order->product;
        if (!$product) {
            return; // No product associated with this order
        }

        // Check if this is a product directly assigned to a warehouse
        if ($product->warehouse_id && $warehouseId && $product->warehouse_id == $warehouseId) {
            $this->updateProductStockFromOrderStatus($order, $oldStatus, $newStatus, $product);
            return;
        }

        // Find the corresponding stock record using the product's SKU
        $stock = \App\Models\Stock::where('reference', $product->sku)->first();
        if (!$stock) {
            return; // No stock record found for this product
        }

        $quantity = $order->quantity;
        $oldStatusLower = strtolower($oldStatus);
        $newStatusLower = strtolower($newStatus);

        // Handle bidirectional status transitions
        switch ($newStatusLower) {
            case 'processing':
            case 'shipped':
                // Moving TO Processing/Shipped (In Progress)
                if ($oldStatusLower === 'delivered' || $oldStatusLower === 'completed') {
                    // Moving FROM Delivered TO In Progress
                    $stock->delivered_quantity = max(0, $stock->delivered_quantity - $quantity);
                    $stock->in_progress_quantity += $quantity;
                } elseif ($oldStatusLower !== 'processing' && $oldStatusLower !== 'shipped') {
                    // Moving FROM New/Pending TO In Progress
                    $stock->in_progress_quantity += $quantity;
                }
                break;

            case 'delivered':
            case 'completed':
                // Moving TO Delivered/Completed
                if ($oldStatusLower === 'processing' || $oldStatusLower === 'shipped') {
                    // Moving FROM In Progress TO Delivered
                    $stock->in_progress_quantity = max(0, $stock->in_progress_quantity - $quantity);
                    $stock->delivered_quantity += $quantity;
                } elseif ($oldStatusLower !== 'delivered' && $oldStatusLower !== 'completed') {
                    // Moving FROM New/Pending TO Delivered (direct delivery)
                    $stock->delivered_quantity += $quantity;
                }
                break;

            case 'cancelled':
            case 'refunded':
            case 'unreachable':
            case 'postponed':
            case 'wrong number':
            case 'out of stock':
            case 'blacklisted':
            case 'new order':
            case 'pending':
                // Moving TO statuses that restore stock quantities
                if ($oldStatusLower === 'processing' || $oldStatusLower === 'shipped') {
                    // Return from In Progress
                    $stock->in_progress_quantity = max(0, $stock->in_progress_quantity - $quantity);
                } elseif ($oldStatusLower === 'delivered' || $oldStatusLower === 'completed') {
                    // Return from Delivered
                    $stock->delivered_quantity = max(0, $stock->delivered_quantity - $quantity);
                }
                break;
        }

        // Save changes and recalculate remaining quantity
        $stock->save();
        $stock->recalculateRemainingQuantity()->save();

        // Update warehouse_stock pivot table if warehouse_id is provided
        if ($warehouseId && $stock->warehouses()->where('warehouse_id', $warehouseId)->exists()) {
            $warehouseStock = $stock->warehouses()->where('warehouse_id', $warehouseId)->first();
            if ($warehouseStock) {
                // Update the warehouse-specific quantity based on status change
                $currentWarehouseQuantity = $warehouseStock->pivot->quantity;
                
                switch (strtolower($newStatus)) {
                    case 'processing':
                    case 'shipped':
                        // Reduce warehouse quantity when order goes to processing/shipped
                        if (strtolower($oldStatus) !== 'processing' && strtolower($oldStatus) !== 'shipped') {
                            $newWarehouseQuantity = max(0, $currentWarehouseQuantity - $quantity);
                            $stock->warehouses()->updateExistingPivot($warehouseId, [
                                'quantity' => $newWarehouseQuantity
                            ]);
                        }
                        break;
                    case 'cancelled':
                    case 'refunded':
                    case 'unreachable':
                    case 'postponed':
                    case 'wrong number':
                    case 'out of stock':
                    case 'blacklisted':
                    case 'new order':
                    case 'pending':
                        // Restore warehouse quantity when order is cancelled/refunded
                        if (strtolower($oldStatus) === 'processing' || strtolower($oldStatus) === 'shipped') {
                            $newWarehouseQuantity = $currentWarehouseQuantity + $quantity;
                            $stock->warehouses()->updateExistingPivot($warehouseId, [
                                'quantity' => $newWarehouseQuantity
                            ]);
                        }
                        break;
                }
            }
        }

        // Update the stock's last updated info
        $stock->update([
            'last_updated_by' => auth()->user()->name,
            'last_updated_at' => now(),
            'notes' => "Updated from order #{$order->id} status change: {$oldStatus} → {$newStatus} on " . now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Validate if warehouse has enough stock for the order
     */
    private function validateWarehouseStock($order, $warehouseId)
    {
        // Get the product from the order
        $product = $order->product;
        if (!$product) {
            return [
                'valid' => false,
                'message' => 'No product associated with this order'
            ];
        }

        // First, check if the product is directly assigned to the warehouse
        if ($product->warehouse_id == $warehouseId) {
            $availableQuantity = $product->stock_quantity;
            $requiredQuantity = $order->quantity;

            if ($availableQuantity >= $requiredQuantity) {
                return [
                    'valid' => true,
                    'message' => 'Stock validation passed (direct product assignment)'
                ];
            } else {
                return [
                    'valid' => false,
                    'message' => "Insufficient stock in warehouse. Available: {$availableQuantity}, Required: {$requiredQuantity}"
                ];
            }
        }

        // If not directly assigned, check for stock records (for products created through shipments)
        $stock = \App\Models\Stock::where('reference', $product->sku)->first();
        if (!$stock) {
            return [
                'valid' => false,
                'message' => "Product '{$product->name}' is not available in the selected warehouse"
            ];
        }

        // Check if the stock is available in the selected warehouse
        $warehouseStock = $stock->warehouses()
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$warehouseStock) {
            return [
                'valid' => false,
                'message' => "Product '{$product->name}' is not available in the selected warehouse"
            ];
        }

        $availableQuantity = $warehouseStock->pivot->quantity;
        $requiredQuantity = $order->quantity;

        if ($availableQuantity < $requiredQuantity) {
            return [
                'valid' => false,
                'message' => "Insufficient stock in warehouse. Available: {$availableQuantity}, Required: {$requiredQuantity}"
            ];
        }

        return [
            'valid' => true,
            'message' => 'Stock validation passed (stock record)'
        ];
    }

    /**
     * Update product stock quantities when product is directly assigned to warehouse
     */
    private function updateProductStockFromOrderStatus($order, $oldStatus, $newStatus, $product)
    {
        $quantity = $order->quantity;
        $oldStatusLower = strtolower($oldStatus);
        $newStatusLower = strtolower($newStatus);

        // Handle status transitions for products directly assigned to warehouses
        switch ($newStatusLower) {
            case 'processing':
            case 'shipped':
                // Moving TO Processing/Shipped - reduce available stock
                if ($oldStatusLower !== 'processing' && $oldStatusLower !== 'shipped') {
                    $newStockQuantity = max(0, $product->stock_quantity - $quantity);
                    $product->update(['stock_quantity' => $newStockQuantity]);
                }
                break;

            case 'cancelled':
            case 'refunded':
            case 'unreachable':
            case 'postponed':
            case 'wrong number':
            case 'out of stock':
            case 'blacklisted':
            case 'new order':
            case 'pending':
                // Moving TO statuses that restore stock quantities
                if ($oldStatusLower === 'processing' || $oldStatusLower === 'shipped') {
                    // Return stock when order is cancelled/refunded
                    $newStockQuantity = $product->stock_quantity + $quantity;
                    $product->update(['stock_quantity' => $newStockQuantity]);
                }
                break;
        }

        // Log the action
        $this->logAction('Product Stock Updated', "Product '{$product->name}' stock updated from order #{$order->id} status change: {$oldStatus} → {$newStatus}", [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'quantity_change' => $quantity,
            'new_stock_quantity' => $product->stock_quantity
        ]);
    }
} 