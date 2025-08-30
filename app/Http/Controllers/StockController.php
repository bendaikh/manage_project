<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::query()->with(['seller', 'shipment', 'product']);
        
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        // Admin, superadmin, manager, and agent roles should see all stocks
        
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
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by warehouse location
        if ($request->filled('warehouse_location')) {
            $query->where('warehouse_location', 'like', "%{$request->warehouse_location}%");
        }
        
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
     * Calculate today's delivered and in-progress quantities for a stock
     */
    private function calculateTodayQuantities($stock)
    {
        $today = now()->toDateString();
        
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
            // First, find products that match the stock title or reference
            $matchingProducts = \App\Models\Product::where(function($q) use ($stock) {
                $q->where('name', 'like', "%{$stock->title}%")
                  ->orWhere('sku', 'like', "%{$stock->reference}%")
                  ->orWhere('name', 'like', "%{$stock->reference}%");
            })->pluck('id');
            
            if ($matchingProducts->count() > 0) {
                $orderQuery->whereIn('product_id', $matchingProducts);
            } else {
                // If no matching products found, return 0
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'title' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'initial_quantity' => 'required|integer|min:0',
            'delivered_quantity' => 'nullable|integer|min:0',
            'damaged_quantity' => 'nullable|integer|min:0',
            'in_progress_quantity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'warehouse_location' => 'nullable|string|max:255',
            'product_link' => 'nullable|url',
            'photo' => 'nullable|image|max:4096',
            'notes' => 'nullable|string',
        ]);
        
        $data['seller_id'] = Auth::id();
        $data['last_updated_by'] = Auth::user()->name;
        $data['last_updated_at'] = now();
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('stocks', 'public');
        }
        
        $stock = Stock::create($data);
        
        // Sync with product if linked
        if ($stock->product_id) {
            $stock->syncWithProduct();
        }
        
        $stock->recalculateRemainingQuantity()->save();
        
        return response()->json(['message' => 'Stock created successfully', 'stock' => $stock], 201);
    }

    public function show($id)
    {
        $stock = Stock::with(['seller', 'shipment', 'product'])->findOrFail($id);
        
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403);
        }
        
        return response()->json($stock);
    }

    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403);
        }
        
        $data = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'title' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'initial_quantity' => 'required|integer|min:0',
            'delivered_quantity' => 'nullable|integer|min:0',
            'damaged_quantity' => 'nullable|integer|min:0',
            'in_progress_quantity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'warehouse_location' => 'nullable|string|max:255',
            'product_link' => 'nullable|url',
            'photo' => 'nullable|image|max:4096',
            'notes' => 'nullable|string',
        ]);
        
        $data['last_updated_by'] = Auth::user()->name;
        $data['last_updated_at'] = now();
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($stock->photo) {
                \Storage::disk('public')->delete($stock->photo);
            }
            $data['photo'] = $request->file('photo')->store('stocks', 'public');
        }
        
        $stock->update($data);
        
        // Sync with product if linked
        if ($stock->product_id) {
            $stock->syncWithProduct();
        }
        
        $stock->recalculateRemainingQuantity()->save();
        
        return response()->json(['message' => 'Stock updated successfully', 'stock' => $stock]);
    }

    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403);
        }
        
        // Delete photo if exists
        if ($stock->photo) {
            \Storage::disk('public')->delete($stock->photo);
        }
        
        $stock->delete();
        return response()->json(['message' => 'Stock deleted successfully']);
    }

    /**
     * Update stock quantities (delivered, damaged, in progress)
     */
    public function updateQuantities(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        
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
     * Get stock statistics
     */
    public function statistics()
    {
        // Base query for seller filtering
        $baseQuery = Stock::query();
        
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $baseQuery->where('seller_id', Auth::id());
        }
        // Admin, superadmin, manager, and agent roles should see all stocks
        
        $stocks = $baseQuery->get();
        
        // Calculate today's totals
        $today = now()->toDateString();
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
     * Get available products for linking to stock
     */
    public function getAvailableProducts(Request $request)
    {
        $query = Product::query();
        
        // Only filter by seller if the user has seller role
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        // Admin, superadmin, manager, and agent roles should see all products
        
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        $products = $query->select('id', 'name', 'sku', 'category')
            ->orderBy('name')
            ->limit(20)
            ->get();
        
        return response()->json($products);
    }

    /**
     * Sync stock with linked product
     */
    public function syncWithProduct($id)
    {
        $stock = Stock::findOrFail($id);
        
        if (Auth::user()->hasRole('seller') && $stock->seller_id !== Auth::id()) {
            abort(403);
        }
        
        if (!$stock->product_id) {
            return response()->json(['message' => 'No product linked to this stock'], 400);
        }
        
        $stock->syncWithProduct();
        $stock->save();
        
        return response()->json([
            'message' => 'Stock synced with product successfully', 
            'stock' => $stock->fresh()
        ]);
    }
} 