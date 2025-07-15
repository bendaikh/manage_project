<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderAssignmentController extends Controller
{
    /**
     * Get available agents for assignment.
     */
    public function getAgents(): JsonResponse
    {
        $agents = User::whereHas('roles', function ($query) {
            $query->where('name', 'agent');
        })->select('id', 'name', 'email')->get();

        return response()->json($agents);
    }

    /**
     * Assign orders to an agent.
     */
    public function assignOrders(Request $request): JsonResponse
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'agent_id' => 'required|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        // Check if the target user is an agent
        $agent = User::find($request->agent_id);
        if (!$agent->isAgent()) {
            return response()->json(['message' => 'Selected user is not an agent'], 400);
        }

        $assignedCount = 0;
        $errors = [];

        foreach ($request->order_ids as $orderId) {
            try {
                // Get the order
                $order = Order::find($orderId);
                if (!$order) {
                    $errors[] = "Order #{$orderId} not found";
                    continue;
                }

                // Check if order is already assigned
                $existingAssignment = OrderAssignment::where('order_id', $orderId)->first();
                
                if ($existingAssignment) {
                    // Update existing assignment
                    $existingAssignment->update([
                        'assigned_to' => $request->agent_id,
                        'assigned_by' => auth()->id(),
                        'notes' => $request->notes,
                        'assigned_at' => now()
                    ]);
                } else {
                    // Create new assignment
                    OrderAssignment::create([
                        'order_id' => $orderId,
                        'assigned_to' => $request->agent_id,
                        'assigned_by' => auth()->id(),
                        'notes' => $request->notes
                    ]);
                }

                // Note: Order status remains "New Order" when assigned to agent
                // Status will be changed to "Confirmed" when agent actually confirms the order
                
                $assignedCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to assign order #{$orderId}: " . $e->getMessage();
            }
        }

        $message = "Successfully assigned {$assignedCount} order(s) to {$agent->name}";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', $errors);
        }

        return response()->json([
            'message' => $message,
            'assigned_count' => $assignedCount,
            'errors' => $errors
        ]);
    }

    /**
     * Get orders assigned to the current user (agent).
     */
    public function getMyAssignedOrders(): JsonResponse
    {
        $orders = auth()->user()->assignedOrders()
            ->with(['product', 'orderStatus', 'assignment.assignedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Get all orders with assignment information (for superadmin).
     */
    public function getAllOrdersWithAssignments(): JsonResponse
    {
        $orders = Order::with(['product', 'orderStatus', 'assignment.assignedTo', 'assignment.assignedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Remove assignment from orders.
     */
    public function removeAssignment(Request $request): JsonResponse
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $removedCount = 0;
        foreach ($request->order_ids as $orderId) {
            $assignment = OrderAssignment::where('order_id', $orderId)->first();
            if ($assignment) {
                $assignment->delete();
                $removedCount++;
            }
        }

        return response()->json([
            'message' => "Successfully removed assignment from {$removedCount} order(s)",
            'removed_count' => $removedCount
        ]);
    }

    /**
     * Get assignment statistics.
     */
    public function getAssignmentStats(): JsonResponse
    {
        $stats = [
            'total_orders' => Order::count(),
            'assigned_orders' => OrderAssignment::count(),
            'unassigned_orders' => Order::whereDoesntHave('assignment')->count(),
            'agents_count' => User::whereHas('roles', function ($query) {
                $query->where('name', 'agent');
            })->count()
        ];

        return response()->json($stats);
    }
}
