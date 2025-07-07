<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;

class OrderController extends Controller
{
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
        $order->status = $order->orderStatus?->name;

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    public function index(Request $request)
    {
        $query = \App\Models\Order::with(['product', 'orderStatus']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('seller', 'like', "%$search%")
                  ->orWhere('client_name', 'like', "%$search%")
                  ->orWhere('client_phone', 'like', "%$search%")
                  ->orWhere('client_address', 'like', "%$search%")
                  ->orWhere('comment', 'like', "%$search%")
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->where('name', 'like', "%$search%")
                        ->orWhere('sku', 'like', "%$search%") ;
                  });
            });
        }
        if ($request->filled('seller')) {
            $query->where('seller', $request->seller);
        }
        if ($request->filled('order_status_id')) {
            $query->where('order_status_id', $request->order_status_id);
        }
        if ($request->filled('agent')) {
            $query->where('agent', $request->agent);
        }
        if ($request->filled('zone')) {
            $query->where('zone', $request->zone);
        }
        if ($request->filled('status')) {
            $statusNames = explode(',', $request->status);
            $query->whereHas('orderStatus', function ($q) use ($statusNames) {
                $q->whereIn('name', $statusNames);
            });
        }
        // Add support for exclude_status
        if ($request->filled('exclude_status')) {
            $excludeStatuses = explode(',', $request->exclude_status);
            $query->whereHas('orderStatus', function ($q) use ($excludeStatuses) {
                $q->whereNotIn('name', $excludeStatuses);
            });
        }
        // Date range filter (not implemented in form, but placeholder)
        // ...

        $ordersCollection = $query->orderByDesc('created_at')->get();

        // Add 'status' attribute as the status name to keep existing frontend working
        $orders = $ordersCollection->map(function ($order) {
            $order->status = $order->orderStatus?->name;
            return $order;
        });

        $sellers = \App\Models\Order::select('seller')->distinct()->pluck('seller')->filter()->values();
        $zones = \App\Models\Order::select('zone')->distinct()->pluck('zone')->filter()->values();

        return response()->json([
            'orders' => $orders,
            'sellers' => $sellers,
            'zones' => $zones,
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
        $order->status = $order->orderStatus?->name;
        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $request->validate([ 'status' => 'required|string' ]);
        $statusModel = \App\Models\OrderStatus::where('name', $request->status)->first();
        if (!$statusModel) {
            return response()->json(['message' => 'Invalid status'], 422);
        }
        $order->order_status_id = $statusModel->id;
        $order->save();
        return response()->json(['message' => 'Status updated', 'order' => $order]);
    }
} 