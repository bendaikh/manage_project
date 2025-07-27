<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Traits\LogsActionHistory;

class OrderController extends Controller
{
    use LogsActionHistory;

    /**
     * Apply date range filter to the query
     */
    private function applyDateRangeFilter($query, $dateRange)
    {
        switch ($dateRange) {
            case 'Today':
                $query->whereDate('created_at', today());
                break;
            case 'Yesterday':
                $query->whereDate('created_at', today()->subDay());
                break;
            case 'This Month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'Last Month':
                $query->whereMonth('created_at', now()->subMonth()->month)
                      ->whereYear('created_at', now()->subMonth()->year);
                break;
            // Add more date ranges as needed
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'order_status_id' => 'nullable|exists:order_statuses,id',
            'belongs_to' => 'nullable|in:confirmation,delivery',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
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
        $query = Order::with(['product', 'orderStatus', 'assignment.assignedTo', 'assignment.assignedBy']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_phone', 'like', "%{$search}%")
                  ->orWhere('client_address', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
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
            $this->applyDateRangeFilter($query, $request->dateRange);
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

        // Build query with filters (already built above $query)

        // Capture counts BEFORE pagination so they reflect the entire filtered dataset
        $countsQuery = clone $query;
        
        // For status blocks, we want to show today's orders only
        $todayCountsQuery = clone $countsQuery;
        $todayCountsQuery->whereDate('orders.created_at', today());
        
        $statusCounts = $todayCountsQuery
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
        })->whereDate('updated_at', today())->count();

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

        $order->update($data);
        
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
            'source' => 'nullable|in:confirmation,delivery' // Add source parameter
        ]);
        
        $statusName = $request->status;
        // If status is 'Confirmed', immediately set to 'Processing'
        if (strtolower($statusName) === 'confirmed') {
            $statusName = 'Processing';
        }
        // Find the status model
        $statusModel = \App\Models\OrderStatus::where('name', $statusName)->first();
        if (!$statusModel) {
            return response()->json(['message' => 'Invalid status'], 422);
        }
        
        $order->order_status_id = $statusModel->id;
        
        // Special case: When confirming an order, always move to delivery
        if (strtolower($request->status) === 'confirmed') {
            $order->belongs_to = 'delivery';
        } else {
            // For all other status changes, set belongs_to based on where the change is coming from
            if ($request->filled('source')) {
                $order->belongs_to = $request->source;
            }
        }
        
        $order->save();
        // Log status change action
        $this->logAction('Order Status Updated', 'Order #' . $order->id . ' status changed to ' . $statusName, [
            'order_id' => $order->id,
            'new_status' => $statusName,
        ]);
        return response()->json([
            'message' => $request->status === 'Confirmed' ? 'Order confirmed and moved to Processing (Delivery)' : 'Status updated',
            'order' => $order
        ]);
    }
} 