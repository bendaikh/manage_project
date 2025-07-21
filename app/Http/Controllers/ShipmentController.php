<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    // List shipments (filtered by seller for sellers, all for admin/agent)
    public function index(Request $request)
    {
        $query = Shipment::query()->with('seller');
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        if ($request->filled('validated')) {
            $query->where('validated', $request->validated ? 1 : 0);
        }
        $shipments = $query->orderByDesc('created_at')->paginate(15);
        return response()->json($shipments);
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
            'shipment_date' => 'required|date',
            'customs_fees' => 'nullable|numeric|min:0',
        ]);
        $data['seller_id'] = Auth::id();
        $data['status'] = 'Processing';
        $data['validated'] = false;
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('shipments', 'public');
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
            'shipment_date' => 'required|date',
            'customs_fees' => 'nullable|numeric|min:0',
        ]);
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($shipment->photo) {
                Storage::disk('public')->delete($shipment->photo);
            }
            $data['photo'] = $request->file('photo')->store('shipments', 'public');
        }
        $shipment->update($data);
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
        if ($shipment->photo) {
            Storage::disk('public')->delete($shipment->photo);
        }
        $shipment->delete();
        return response()->json(['message' => 'Shipment deleted']);
    }

    // Toggle validation (admin/agent only)
    public function validateShipment($id)
    {
        $shipment = Shipment::findOrFail($id);
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('agent') && !Auth::user()->hasRole('superadmin')) {
            abort(403);
        }
        $shipment->validated = !$shipment->validated;
        $shipment->status = $shipment->validated ? 'Validated' : 'Processing';
        $shipment->save();

        // Sync with Stock table
        if ($shipment->validated) {
            \App\Models\Stock::updateOrCreate(
                ['shipment_id' => $shipment->id],
                [
                    'seller_id' => $shipment->seller_id,
                    'title' => $shipment->title,
                    'quantity' => $shipment->quantity,
                    'description' => $shipment->description,
                ]
            );
        } else {
            // If un-validated, remove from stock
            \App\Models\Stock::where('shipment_id', $shipment->id)->delete();
        }
        return response()->json(['message' => 'Shipment validation updated', 'shipment' => $shipment]);
    }
} 