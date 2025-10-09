<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Shipment;

class ProductOffersController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('view_product_offers')) {
            abort(403, 'You do not have permission to view product offers.');
        }

        // Only show products that have corresponding validated shipments
        $validatedShipmentReferences = Shipment::where('validated', true)->pluck('reference');
        
        $query = Product::with('seller')
            ->whereIn('sku', $validatedShipmentReferences)
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                      ->from('shipments')
                      ->whereColumn('shipments.reference', 'products.sku')
                      ->where('shipments.validated', true);
            });

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search){
                $q->where('name','like',"%{$search}%")
                  ->orWhere('sku','like',"%{$search}%")
                  ->orWhere('seller','like',"%{$search}%");
            });
        }

        $products = $query->orderByDesc('created_at')->paginate(20);

        // Append can_toggle and clean up seller data (all products here have validated shipments)
        $products->getCollection()->transform(function($product){
            // Since we only show products with validated shipments, toggle is always enabled
            $product->can_toggle = true;
            
            // Clean up seller data - show only the name instead of the full object
            if ($product->seller && is_object($product->seller)) {
                $product->seller_name = $product->seller->name;
                unset($product->seller); // Remove the full seller object
            } else {
                $product->seller_name = $product->seller ?: 'N/A';
            }
            
            // Add assigned sellers count for marketplace visibility info
            $product->assigned_sellers_count = $product->assignedSellers()->count();
            
            return $product;
        });

        return response()->json($products);
    }

    public function toggle(Request $request, $productId)
    {
        if (!auth()->user()->hasPermission('toggle_product_offers')) {
            abort(403, 'You do not have permission to toggle product offers.');
        }

        $product = Product::findOrFail($productId);

        $shipment = Shipment::where('reference', $product->sku)->first();
        if (!$shipment) {
            return response()->json(['message' => 'No source shipment found for this product.'], 422);
        }

        // Allow toggle for any product with a validated shipment
        // (Removed restriction that required shipment to be marked as company product)

        $activate = (bool)$request->input('activate', true);

        if ($activate) {
            // ACTIVATE: Make visible in marketplace for all sellers
            $product->is_company_product = true;
            $product->save();

            // Get all active sellers
            $sellerIds = \App\Models\User::whereHas('roles', function($q){
                $q->where('name','seller');
            })->where('is_active', true)->pluck('id');
            
            if ($sellerIds->isNotEmpty()) {
                // Assign product to all sellers (marketplace visibility)
                $product->assignedSellers()->sync($sellerIds->all());
            }

            // Reload the product to get updated relationships
            $product->load('assignedSellers');
            $sellerCount = $product->assignedSellers->count();

            return response()->json([
                'success' => true,
                'message' => "Product activated! Now visible in marketplace for {$sellerCount} sellers.",
                'product' => $product,
                'marketplace_status' => 'visible',
                'assigned_sellers_count' => $sellerCount
            ]);
        } else {
            // DEACTIVATE: Hide from marketplace
            $product->is_company_product = false;
            
            // Remove from all sellers' marketplace
            $product->assignedSellers()->detach();
            $product->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Product deactivated! Hidden from marketplace for all sellers.',
                'product' => $product,
                'marketplace_status' => 'hidden',
                'assigned_sellers_count' => 0
            ]);
        }
    }
}


