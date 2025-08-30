<?php

namespace App\Http\Controllers;

use App\Models\WarehouseTransfer;
use App\Models\Warehouse;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WarehouseTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = WarehouseTransfer::with(['fromWarehouse', 'toWarehouse', 'stock', 'user']);

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $query->whereHas('stock', function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Get paginated results
        $transfers = $query->orderByDesc('created_at')->paginate(15);

        return response()->json($transfers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Check if stock has enough quantity in the source warehouse
        $stock = Stock::find($validated['stock_id']);
        if ($stock->warehouse_id != $validated['from_warehouse_id']) {
            return response()->json([
                'message' => 'Stock is not available in the source warehouse'
            ], 422);
        }

        if ($stock->remaining_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock quantity for transfer'
            ], 422);
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';

        $transfer = WarehouseTransfer::create($validated);

        return response()->json([
            'message' => 'Transfer created successfully',
            'transfer' => $transfer->load(['fromWarehouse', 'toWarehouse', 'stock', 'user'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(WarehouseTransfer $warehouseTransfer): JsonResponse
    {
        return response()->json($warehouseTransfer->load(['fromWarehouse', 'toWarehouse', 'stock', 'user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WarehouseTransfer $warehouseTransfer): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_transit,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Store the old status before updating
        $oldStatus = $warehouseTransfer->status;
        
        $warehouseTransfer->update($validated);

        // If transfer is completed and it wasn't completed before, update stock warehouse quantities
        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $stock = $warehouseTransfer->stock;
            
            // Reduce quantity from source warehouse
            $sourceWarehouseStock = $stock->warehouses()
                                         ->where('warehouse_id', $warehouseTransfer->from_warehouse_id)
                                         ->first();
            
            if ($sourceWarehouseStock) {
                $newSourceQuantity = max(0, $sourceWarehouseStock->pivot->quantity - $warehouseTransfer->quantity);
                $stock->warehouses()->updateExistingPivot($warehouseTransfer->from_warehouse_id, [
                    'quantity' => $newSourceQuantity
                ]);
            }
            
            // Add quantity to destination warehouse
            $destWarehouseStock = $stock->warehouses()
                                       ->where('warehouse_id', $warehouseTransfer->to_warehouse_id)
                                       ->first();
            
            if ($destWarehouseStock) {
                $newDestQuantity = $destWarehouseStock->pivot->quantity + $warehouseTransfer->quantity;
                $stock->warehouses()->updateExistingPivot($warehouseTransfer->to_warehouse_id, [
                    'quantity' => $newDestQuantity
                ]);
            } else {
                // Create new record for destination warehouse
                $stock->warehouses()->attach($warehouseTransfer->to_warehouse_id, [
                    'quantity' => $warehouseTransfer->quantity
                ]);
            }
            
            // Update the main warehouse_id to the destination warehouse
            $stock->warehouse_id = $warehouseTransfer->to_warehouse_id;
            $stock->save();
            

        }

        return response()->json([
            'message' => 'Transfer updated successfully',
            'transfer' => $warehouseTransfer->load(['fromWarehouse', 'toWarehouse', 'stock', 'user'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseTransfer $warehouseTransfer): JsonResponse
    {
        if ($warehouseTransfer->status === 'completed') {
            return response()->json([
                'message' => 'Cannot delete a completed transfer.'
            ], 422);
        }

        $warehouseTransfer->delete();

        return response()->json([
            'message' => 'Transfer deleted successfully'
        ]);
    }

    /**
     * Get available stocks for transfer from a specific warehouse
     */
    public function getStocksByWarehouse(Request $request): JsonResponse
    {
        $warehouseId = $request->validate(['warehouse_id' => 'required|exists:warehouses,id'])['warehouse_id'];
        
        $stocks = Stock::whereHas('warehouses', function ($query) use ($warehouseId) {
                        $query->where('warehouse_id', $warehouseId)
                              ->where('quantity', '>', 0);
                    })
                    ->get(['id', 'title', 'reference'])
                    ->map(function ($stock) use ($warehouseId) {
                        $warehouseStock = $stock->warehouses()
                                               ->where('warehouse_id', $warehouseId)
                                               ->first();
                        $stock->warehouse_quantity = $warehouseStock ? $warehouseStock->pivot->quantity : 0;
                        return $stock;
                    });

        return response()->json($stocks);
    }
}
