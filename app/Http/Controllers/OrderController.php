<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;

class OrderController extends Controller
{
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
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
        if (empty($data['order_status_id'])) {
            $defaultStatus = \App\Models\OrderStatus::where('name', 'New Order')->first();
            $data['order_status_id'] = $defaultStatus ? $defaultStatus->id : null;
        }

        $order = Order::create($data);

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

        // Apply pagination (default 10 items per page)
        $perPage = $request->input('per_page', 10);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get unique values for filters
        $sellers = Order::distinct()->pluck('seller')->filter()->values();
        $zones = Order::distinct()->pluck('zone')->filter()->values();

        return response()->json([
            'orders' => $orders,
            'sellers' => $sellers,
            'zones' => $zones
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

        $order->update($data);
        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $request->validate([ 'status' => 'required|string' ]);
        
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
        $order->save();
        return response()->json([
            'message' => $request->status === 'Confirmed' ? 'Order confirmed and moved to Processing (Delivery)' : 'Status updated',
            'order' => $order
        ]);
    }
} 