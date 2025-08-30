<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Models\Warehouse; // Added import for Warehouse

class ShipmentController extends Controller
{
    // List shipments (filtered by seller for sellers, all for admin/agent)
    public function index(Request $request)
    {
        $query = Shipment::query()->with('seller');
        
        // Filter by seller role
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        
        // Keyword search across multiple fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('link', 'like', "%{$search}%");
            });
        }
        
        // Filter by validation status
        if ($request->filled('validated')) {
            $query->where('validated', $request->validated ? 1 : 0);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('shipment_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('shipment_date', '<=', $request->date_to);
        }
        
        // Filter by quantity range
        if ($request->filled('quantity_min')) {
            $query->where('quantity', '>=', $request->quantity_min);
        }
        
        if ($request->filled('quantity_max')) {
            $query->where('quantity', '<=', $request->quantity_max);
        }
        
        // Filter by customs fees range
        if ($request->filled('fees_min')) {
            $query->where('customs_fees', '>=', $request->fees_min);
        }
        
        if ($request->filled('fees_max')) {
            $query->where('customs_fees', '<=', $request->fees_max);
        }
        
        $shipments = $query->orderByDesc('created_at')->paginate(15);
        return response()->json($shipments);
    }

    // Get available stocks for dropdown
    public function getStocks()
    {
        $query = Stock::query()->with('seller');
        
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        
        $stocks = $query->orderBy('title')->get();
        return response()->json($stocks);
    }

    // Store a new shipment
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'link' => 'required|url',
            'photo' => 'nullable|image|max:4096',
            'shipment_date' => 'required|date_format:Y-m-d',
            'customs_fees' => 'nullable|numeric|min:0',
            'stock_id' => 'nullable|exists:stocks,id', // New field for stock selection
            'seller_id' => 'nullable|exists:users,id', // New field for seller selection
        ]);
        
        // Set seller_id based on user role
        if (Auth::user()->hasRole('seller')) {
            $data['seller_id'] = Auth::id(); // Sellers can only create shipments for themselves
        } else {
            $data['seller_id'] = $request->seller_id ?? Auth::id(); // Admins/managers can select seller
        }
        
        $data['status'] = 'Processing';
        $data['validated'] = false;
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('shipments', 'public');
        }
        
        // If stock_id is provided, copy data from stock
        if ($request->filled('stock_id')) {
            $stock = Stock::find($request->stock_id);
            if ($stock) {
                $data['title'] = $stock->title;
                $data['description'] = $stock->description;
                $data['reference'] = $stock->reference;
                // Keep the quantity from the form as it might be different
            }
        }
        
        $shipment = Shipment::create($data);
        return response()->json(['message' => 'Shipment created', 'shipment' => $shipment], 201);
    }

    // Show a shipment
    public function show($id)
    {
        $shipment = Shipment::with('seller')->findOrFail($id);
        if (Auth::user()->hasRole('seller') && $shipment->seller_id !== Auth::id()) {
            abort(403);
        }
        return response()->json($shipment);
    }

    // Update a shipment (only if not validated and owned by seller)
    public function update(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        if ($shipment->validated) {
            return response()->json(['message' => 'Cannot edit a validated shipment'], 403);
        }
        if (Auth::user()->hasRole('seller') && $shipment->seller_id !== Auth::id()) {
            abort(403);
        }
        
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'link' => 'required|url',
            'photo' => 'nullable|image|max:4096',
            'shipment_date' => 'required|date_format:Y-m-d',
            'customs_fees' => 'nullable|numeric|min:0',
            'stock_id' => 'nullable|exists:stocks,id', // New field for stock selection
            'seller_id' => 'nullable|exists:users,id', // New field for seller selection
        ]);
        
        // Set seller_id based on user role
        if (Auth::user()->hasRole('seller')) {
            $data['seller_id'] = Auth::id(); // Sellers can only update shipments for themselves
        } else {
            $data['seller_id'] = $request->seller_id ?? $shipment->seller_id; // Admins/managers can change seller
        }
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($shipment->photo) {
                Storage::disk('public')->delete($shipment->photo);
            }
            $data['photo'] = $request->file('photo')->store('shipments', 'public');
        }
        
        // If stock_id is provided, copy data from stock
        if ($request->filled('stock_id')) {
            $stock = Stock::find($request->stock_id);
            if ($stock) {
                $data['title'] = $stock->title;
                $data['description'] = $stock->description;
                $data['reference'] = $stock->reference;
                // Keep the quantity from the form as it might be different
            }
        }
        
        $shipment->update($data);
        $shipment->refresh(); // Refresh the model to get the latest data
        return response()->json(['message' => 'Shipment updated', 'shipment' => $shipment]);
    }

    // Delete a shipment (only if not validated and owned by seller)
    public function destroy($id)
    {
        $shipment = Shipment::findOrFail($id);
        if ($shipment->validated) {
            return response()->json(['message' => 'Cannot delete a validated shipment'], 403);
        }
        if (Auth::user()->hasRole('seller') && $shipment->seller_id !== Auth::id()) {
            abort(403);
        }
        
        // Delete photo if exists
        if ($shipment->photo) {
            Storage::disk('public')->delete($shipment->photo);
        }
        
        $shipment->delete();
        return response()->json(['message' => 'Shipment deleted']);
    }

    // Toggle validation (admin/agent only)
    public function validateShipment(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('agent') && !Auth::user()->hasRole('superadmin')) {
            abort(403);
        }
        
        $shipment->validated = !$shipment->validated;
        $shipment->status = $shipment->validated ? 'Validated' : 'Processing';
        $shipment->save();

        // Comprehensive stock synchronization
        if ($shipment->validated) {
            $warehouseId = $request->input('warehouse_id');
            $this->syncShipmentToStock($shipment, $warehouseId);
        } else {
            // If un-validated, remove from stock and products
            Stock::where('shipment_id', $shipment->id)->delete();
            // Also remove the corresponding product
            \App\Models\Product::where('sku', $shipment->reference)->delete();
        }
        
        return response()->json(['message' => 'Shipment validation updated', 'shipment' => $shipment]);
    }

    /**
     * Get all active warehouses for shipment validation
     */
    public function getWarehouses()
    {
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'location']);
        return response()->json($warehouses);
    }

    /**
     * Sync shipment data to stock table and create product with comprehensive inventory management
     */
    private function syncShipmentToStock($shipment, $warehouseId = null)
    {
        // Check if stock record already exists for this shipment
        $existingStock = Stock::where('shipment_id', $shipment->id)->first();
        
        if ($existingStock) {
            // Update existing stock record
            $existingStock->update([
                'seller_id' => $shipment->seller_id,
                'title' => $shipment->title,
                'reference' => $shipment->reference,
                'barcode' => $shipment->reference, // Use reference as barcode if no separate barcode
                'quantity' => $shipment->quantity,
                'initial_quantity' => $shipment->quantity,
                'delivered_quantity' => 0, // Start with 0 delivered
                'damaged_quantity' => 0, // Start with 0 damaged
                'in_progress_quantity' => 0, // Start with 0 in progress
                'description' => $shipment->description,
                'product_link' => $shipment->link,
                'photo' => $shipment->photo,
                'status' => 'in_stock',
                'warehouse_id' => $warehouseId,
                'last_updated_by' => Auth::user()->name,
                'last_updated_at' => now(),
                'notes' => "Validated from shipment #{$shipment->id} on " . now()->format('Y-m-d H:i:s'),
            ]);
            
            $existingStock->recalculateRemainingQuantity()->save();
            
            // Update warehouse_stock pivot table
            if ($warehouseId) {
                $existingStock->warehouses()->syncWithoutDetaching([
                    $warehouseId => ['quantity' => $existingStock->remaining_quantity]
                ]);
            }
        } else {
            // Check if this shipment was created from existing stock (same reference)
            $existingStockByReference = Stock::where('reference', $shipment->reference)
                                            ->where('seller_id', $shipment->seller_id)
                                            ->first();
            
            if ($existingStockByReference) {
                // Consolidate with existing stock - increase initial quantity
                $existingStockByReference->update([
                    'initial_quantity' => $existingStockByReference->initial_quantity + $shipment->quantity,
                    'last_updated_by' => Auth::user()->name,
                    'last_updated_at' => now(),
                    'notes' => "Consolidated shipment #{$shipment->id} (+{$shipment->quantity} qty) on " . now()->format('Y-m-d H:i:s'),
                ]);
                
                $existingStockByReference->recalculateRemainingQuantity()->save();
                
                // Update warehouse_stock pivot table
                if ($warehouseId) {
                    $existingStockByReference->warehouses()->syncWithoutDetaching([
                        $warehouseId => ['quantity' => $existingStockByReference->remaining_quantity]
                    ]);
                }
                
                // Link this shipment to the existing stock record
                $shipment->update(['stock_id' => $existingStockByReference->id]);
                
            } else {
                // Create new stock record
                $stock = Stock::create([
                    'seller_id' => $shipment->seller_id,
                    'shipment_id' => $shipment->id,
                    'title' => $shipment->title,
                    'reference' => $shipment->reference,
                    'barcode' => $shipment->reference, // Use reference as barcode if no separate barcode
                    'quantity' => $shipment->quantity,
                    'initial_quantity' => $shipment->quantity,
                    'delivered_quantity' => 0,
                    'damaged_quantity' => 0,
                    'in_progress_quantity' => 0,
                    'description' => $shipment->description,
                    'product_link' => $shipment->link,
                    'photo' => $shipment->photo,
                    'status' => 'in_stock',
                    'warehouse_id' => $warehouseId,
                    'last_updated_by' => Auth::user()->name,
                    'last_updated_at' => now(),
                    'notes' => "Created from validated shipment #{$shipment->id} on " . now()->format('Y-m-d H:i:s'),
                ]);
                
                $stock->recalculateRemainingQuantity()->save();
                
                // Update warehouse_stock pivot table
                if ($warehouseId) {
                    $stock->warehouses()->syncWithoutDetaching([
                        $warehouseId => ['quantity' => $stock->remaining_quantity]
                    ]);
                }
            }
        }

        // Also create/update product in the products table
        $this->syncShipmentToProduct($shipment);
    }

    /**
     * Sync shipment data to product table
     */
    private function syncShipmentToProduct($shipment)
    {
        // Get seller name
        $sellerName = $shipment->seller ? $shipment->seller->name : 'Unknown Seller';
        
        // Check if product already exists for this shipment
        $existingProduct = \App\Models\Product::where('sku', $shipment->reference)->first();
        
        if ($existingProduct) {
            // Get total stock quantity for this reference
            $totalStockQuantity = \App\Models\Stock::where('reference', $shipment->reference)
                                                  ->where('seller_id', $shipment->seller_id)
                                                  ->sum('initial_quantity');
            
            // Update existing product with total stock quantity
            $existingProduct->update([
                'name' => $shipment->title,
                'sku' => $shipment->reference,
                'category' => 'Shipment Products', // Default category
                'supplier' => $sellerName,
                'seller_id' => $shipment->seller_id,
                'seller' => $sellerName,
                'purchase_price' => 0, // Default purchase price, can be updated later
                'selling_price' => 0, // Default selling price, can be updated later
                'stock_quantity' => $totalStockQuantity, // Use total from all consolidated stock
                'status' => 'In Stock',
                'image_url' => $shipment->photo ? "/storage/{$shipment->photo}" : null,
                'video_url' => null,
                'video_duration' => null,
                'description' => $shipment->description,
            ]);
        } else {
            // Create new product
            \App\Models\Product::create([
                'name' => $shipment->title,
                'sku' => $shipment->reference,
                'category' => 'Shipment Products', // Default category
                'supplier' => $sellerName,
                'seller_id' => $shipment->seller_id,
                'seller' => $sellerName,
                'purchase_price' => 0, // Default purchase price, can be updated later
                'selling_price' => 0, // Default selling price, can be updated later
                'stock_quantity' => $shipment->quantity,
                'status' => 'In Stock',
                'image_url' => $shipment->photo ? "/storage/{$shipment->photo}" : null,
                'video_url' => null,
                'video_duration' => null,
                'description' => $shipment->description,
            ]);
        }
    }
} 