<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::query();

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Get paginated results
        $warehouses = $query->orderBy('name')->paginate(15);

        return response()->json($warehouses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        $warehouse = Warehouse::create($validated);

        return response()->json([
            'message' => 'Warehouse created successfully',
            'warehouse' => $warehouse
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        return response()->json($warehouse);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'message' => 'Warehouse updated successfully',
            'warehouse' => $warehouse
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        // Check if warehouse has associated stocks or products
        if ($warehouse->stocks()->count() > 0 || $warehouse->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete warehouse. It has associated stocks or products.'
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'message' => 'Warehouse deleted successfully'
        ]);
    }

    /**
     * Get all products/stocks in a specific warehouse
     */
    public function getProducts(Warehouse $warehouse): JsonResponse
    {
        $stocks = $warehouse->stocks()
                           ->with(['seller', 'product'])
                           ->orderBy('title')
                           ->get()
                           ->map(function ($stock) {
                               // Add warehouse-specific quantity from pivot
                               $stock->warehouse_quantity = $stock->pivot->quantity;
                               return $stock;
                           });

        return response()->json([
            'warehouse' => $warehouse,
            'stocks' => $stocks
        ]);
    }
}
