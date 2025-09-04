<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Order;
use App\Models\OrderStatus;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('superadmin')) {
            return view('dashboard.superadmin');
        } elseif ($user->hasRole('admin')) {
            return view('dashboard.admin');
        } elseif ($user->hasRole('manager')) {
            return view('dashboard.manager');
        } else {
            return view('dashboard.agent');
        }
    }

    public function admin()
    {
        return view('dashboard.admin');
    }

    public function manager()
    {
        return view('dashboard.manager');
    }

    public function agent()
    {
        return view('dashboard.agent');
    }

    public function overviewData(Request $request)
    {
        $start = Carbon::parse($request->input('start_date', Carbon::today()->toDateString()))->startOfDay();
        $end = Carbon::parse($request->input('end_date', Carbon::today()->toDateString()))->endOfDay();

        $ordersQuery = Order::with(['orderStatus', 'assignment.assignedTo'])->whereBetween('created_at', [$start, $end]);
        
        // Auto-filter for seller role
        if (auth()->check() && auth()->user()->hasRole('seller')) {
            $ordersQuery->where('seller', auth()->user()->name);
        }
        
        // Apply additional filters
        if ($request->filled('agent')) {
            $ordersQuery->whereHas('assignment', function ($query) use ($request) {
                $query->whereHas('assignedTo', function ($userQuery) use ($request) {
                    $userQuery->where('name', $request->agent);
                });
            });
        }
        if ($request->filled('seller')) {
            $ordersQuery->where('seller', $request->seller);
        }
        if ($request->filled('product_id')) {
            $ordersQuery->where('product_id', $request->product_id);
        }
        
        $orders = $ordersQuery->get();

        $totalOrders = $orders->count();
        $confirmedOrders = $orders->filter(fn($o) => $o->orderStatus?->name === 'Confirmed')->count();
        $cancelledOrders = $orders->filter(fn($o) => $o->orderStatus?->name === 'Cancelled')->count();
        $pendingOrders = $orders->filter(fn($o) => in_array($o->orderStatus?->name, ['New Order', 'Pending Payment', 'Processing']))->count();

        $days = $start->diffInDays($end) + 1;
        $prevStart = $start->copy()->subDays($days);
        $prevEnd = $start->copy()->subDay();
        $prevTotal = Order::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $totalChange = $prevTotal ? round((($totalOrders - $prevTotal) / max($prevTotal,1)) * 100, 1) : null;

        $distribution = $orders->groupBy(fn($o) => $o->orderStatus?->name ?? 'Unknown')
                                ->map(fn($group) => $group->count());

        $trend = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            $d = $date->format('Y-m-d');
            $daily = $orders->filter(fn($o) => $o->created_at->format('Y-m-d') === $d);
            $trend[] = [
                'date' => $d,
                'all' => $daily->count(),
                'confirmed' => $daily->filter(fn($o) => $o->orderStatus?->name === 'Confirmed')->count(),
                'cancelled' => $daily->filter(fn($o) => $o->orderStatus?->name === 'Cancelled')->count(),
            ];
        }

        return response()->json([
            'summary' => [
                'total' => $totalOrders,
                'confirmed' => $confirmedOrders,
                'cancelled' => $cancelledOrders,
                'pending' => $pendingOrders,
                'total_change' => $totalChange,
            ],
            'distribution' => $distribution,
            'trend' => $trend,
            'orders' => $orders, // Include orders with belongs_to field for frontend filtering
        ]);
    }

    public function analyticsData(Request $request)
    {
        $start = Carbon::parse($request->input('start_date', Carbon::today()->toDateString()))->startOfDay();
        $end = Carbon::parse($request->input('end_date', Carbon::today()->toDateString()))->endOfDay();

        $query = Order::with(['orderStatus', 'product', 'assignment.assignedTo']);
        $query->whereBetween('created_at', [$start, $end]);
        // Auto-filter for seller role
        if (auth()->check() && auth()->user()->hasRole('seller')) {
            $query->where('seller', auth()->user()->name);
        }
        if ($request->filled('seller')) {
            $query->where('seller', $request->seller);
        }
        if ($request->filled('agent')) {
            $query->whereHas('assignment', function ($query) use ($request) {
                $query->whereHas('assignedTo', function ($userQuery) use ($request) {
                    $userQuery->where('name', $request->agent);
                });
            });
        }
        if ($request->filled('zone')) {
            $query->where('zone', $request->zone);
        }
        if ($request->filled('order_status_id')) {
            $query->where('order_status_id', $request->order_status_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $orders = $query->get();

        $revenue = $orders->sum(fn($o) => $o->price);
        // Placeholder profit: assume 20% margin
        $profit = round($revenue * 0.2, 2);
        $totalOrders = $orders->count();
        $activeSellers = $orders->pluck('seller')->filter()->unique()->count();
        $avgOrderValue = $totalOrders ? round($revenue / $totalOrders, 2) : 0;

        $revenueBySeller = $orders->groupBy('seller')->map(fn($g) => $g->sum('price'));
        $performanceByAgent = $orders->groupBy('agent')->map(fn($g) => [
            'revenue' => $g->sum('price'),
            'profit' => round($g->sum('price') * 0.2, 2)
        ]);
        $statusDistribution = $orders->groupBy(fn($o) => $o->orderStatus?->name ?? 'Unknown')->map->count();
        $revenueByZone = $orders->groupBy('zone')->map(fn($g) => $g->sum('price'));

        return response()->json([
            'summary' => [
                'revenue' => $revenue,
                'profit' => $profit,
                'total_orders' => $totalOrders,
                'active_sellers' => $activeSellers,
                'avg_order_value' => $avgOrderValue,
            ],
            'revenue_by_seller' => $revenueBySeller,
            'performance_by_agent' => $performanceByAgent,
            'status_distribution' => $statusDistribution,
            'revenue_by_zone' => $revenueByZone,
            'orders' => $orders,
        ]);
    }

    public function todoWorkData()
    {
        $today = Carbon::today();
        $user = auth()->user();
        
        // Base query with agent filtering
        $baseQuery = function($query) use ($user) {
            // Filter by agent if user is an agent
            if ($user->hasRole('agent')) {
                $query->whereHas('assignment', function ($assignmentQuery) use ($user) {
                    $assignmentQuery->whereHas('assignedTo', function ($userQuery) use ($user) {
                        $userQuery->where('name', $user->name);
                    });
                });
            }
            // Filter by seller if user is a seller
            elseif ($user->hasRole('seller')) {
                $query->where('seller', $user->name);
            }
        };
        
        // Confirmation Section
        // 1. New Order - Show new orders of the day
        $newOrderQuery = Order::whereDate('created_at', $today)
            ->where('belongs_to', 'confirmation')
            ->whereHas('orderStatus', function($query) {
                $query->where('name', 'New Order');
            });
        $baseQuery($newOrderQuery);
        $newOrderCount = $newOrderQuery->count();

        // 2. Confirm on Date - Show orders where today matches the confirmed_date AND status is "Confirmed on Date"
        $confirmOnDateQuery = Order::where('belongs_to', 'confirmation')
            ->whereDate('confirmed_date', $today)
            ->whereNotNull('confirmed_date')
            ->whereHas('orderStatus', function($query) {
                $query->where('name', 'Confirmed on Date');
            });
        $baseQuery($confirmOnDateQuery);
        $confirmOnDateCount = $confirmOnDateQuery->count();

        // 3. Postponed - Show postponed orders where today matches the postponed_date
        $postponedQuery = Order::where('belongs_to', 'confirmation')
            ->whereDate('postponed_date', $today)
            ->whereNotNull('postponed_date')
            ->whereHas('orderStatus', function($query) {
                $query->where('name', 'Postponed');
            });
        $baseQuery($postponedQuery);
        $postponedCount = $postponedQuery->count();

        // Delivery Section
        // 1. Postponed - Show postponed orders where today matches the postponed_date for delivery
        $deliveryPostponedQuery = Order::where('belongs_to', 'delivery')
            ->whereDate('postponed_date', $today)
            ->whereNotNull('postponed_date')
            ->whereHas('orderStatus', function($query) {
                $query->where('name', 'Postponed');
            });
        $baseQuery($deliveryPostponedQuery);
        $deliveryPostponedCount = $deliveryPostponedQuery->count();

        return response()->json([
            'confirmation' => [
                'newOrder' => $newOrderCount,
                'confirmOnDate' => $confirmOnDateCount,
                'postponed' => $postponedCount
            ],
            'delivery' => [
                'postponed' => $deliveryPostponedCount
            ]
        ]);
    }
}
