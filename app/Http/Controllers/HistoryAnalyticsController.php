<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActionHistory;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HistoryAnalyticsController extends Controller
{
    public function analytics(Request $request)
    {
        // Check if user is authenticated and is superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            return response()->json(['message' => 'Unauthorized access. Superadmin role required.'], 403);
        }

        $period = $request->input('period', 'last_7_days');
        $dateRange = $this->getDateRange($period, $request);
        
        $analytics = $this->getAnalytics($dateRange);
        $agents = $this->getAgentPerformance($dateRange);
        $insights = $this->getBusinessInsights($dateRange, $agents);
        $activityTimeline = $this->getActivityTimeline($dateRange);
        $actionTypes = $this->getActionTypes($dateRange);

        return response()->json([
            'analytics' => $analytics,
            'agents' => $agents,
            'insights' => $insights,
            'activityTimeline' => $activityTimeline,
            'actionTypes' => $actionTypes
        ]);
    }

    private function getDateRange($period, $request)
    {
        switch ($period) {
            case 'today':
                return [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];
            case 'yesterday':
                return [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()];
            case 'last_7_days':
                return [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()];
            case 'last_30_days':
                return [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'last_month':
                return [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];
            case 'custom':
                $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->subDays(7)->startOfDay();
                $to = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : Carbon::now()->endOfDay();
                return [$from, $to];
            default:
                return [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()];
        }
    }

    private function getAnalytics($dateRange)
    {
        [$from, $to] = $dateRange;

        $totalActions = ActionHistory::whereBetween('created_at', [$from, $to])->count();
        
        $activeAgents = ActionHistory::whereBetween('created_at', [$from, $to])
            ->whereHas('user.roles', function($query) {
                $query->where('name', 'agent');
            })
            ->distinct('user_id')
            ->count('user_id');
        
        $ordersToday = Order::whereDate('created_at', Carbon::today())->count();
        
        $avgActionsPerAgent = $activeAgents > 0 ? round($totalActions / $activeAgents, 1) : 0;

        return [
            'totalActions' => $totalActions,
            'activeAgents' => $activeAgents,
            'ordersToday' => $ordersToday,
            'avgActionsPerAgent' => $avgActionsPerAgent
        ];
    }

    private function getAgentPerformance($dateRange)
    {
        [$from, $to] = $dateRange;

        // Get ONLY users with 'agent' role who have performed actions in the date range
        $agentActions = ActionHistory::whereBetween('created_at', [$from, $to])
            ->whereHas('user.roles', function($query) {
                $query->where('name', 'agent');
            })
            ->select('user_id', 
                DB::raw('COUNT(*) as total_actions'),
                DB::raw('SUM(CASE WHEN title = "Order Created" THEN 1 ELSE 0 END) as orders_created'),
                DB::raw('SUM(CASE WHEN title LIKE "%Order%" AND title != "Order Created" THEN 1 ELSE 0 END) as orders_updated')
            )
            ->groupBy('user_id')
            ->get();

        $agents = [];
        foreach ($agentActions as $agentAction) {
            $user = User::find($agentAction->user_id);
            if (!$user || !$user->hasRole('agent')) continue; // Double-check: only agents
            $totalActions = $agentAction->total_actions;
            $ordersCreated = $agentAction->orders_created;
            $ordersUpdated = $agentAction->orders_updated;
            
            // Calculate success rate based on order completion
            $successfulActions = $ordersCreated + ($ordersUpdated * 0.5); // Weight updates as half successful
            $successRate = $totalActions > 0 ? round(($successfulActions / $totalActions) * 100, 1) : 0;
            
            // Determine performance level
            $performance = $this->getPerformanceLevel($totalActions, $successRate);
            
            // Generate recommendation
            $recommendation = $this->getAgentRecommendation($totalActions, $successRate, $ordersCreated, $ordersUpdated);

            $agents[] = [
                'id' => $user->id,
                'name' => $user->name,
                'totalActions' => $totalActions,
                'ordersCreated' => $ordersCreated,
                'ordersUpdated' => $ordersUpdated,
                'successRate' => $successRate,
                'performance' => $performance,
                'recommendation' => $recommendation
            ];
        }

        return $agents;
    }

    private function getPerformanceLevel($totalActions, $successRate)
    {
        if ($totalActions >= 50 && $successRate >= 80) return 'Excellent';
        if ($totalActions >= 25 && $successRate >= 60) return 'Good';
        if ($totalActions >= 10 && $successRate >= 40) return 'Average';
        return 'Needs Improvement';
    }

    private function getAgentRecommendation($totalActions, $successRate, $ordersCreated, $ordersUpdated)
    {
        if ($totalActions < 10) {
            return 'Encourage more activity and provide training';
        }
        
        if ($successRate < 40) {
            return 'Focus on quality over quantity, provide mentoring';
        }
        
        if ($successRate >= 80) {
            return 'Performing excellently, consider for leadership role';
        }
        
        if ($ordersCreated > $ordersUpdated * 2) {
            return 'Strong at order creation, train on follow-up processes';
        }
        
        if ($ordersUpdated > $ordersCreated * 2) {
            return 'Good at order management, encourage new order creation';
        }
        
        return 'Consistent performance, provide growth opportunities';
    }

    private function getBusinessInsights($dateRange, $agents)
    {
        [$from, $to] = $dateRange;
        $insights = [];

        // Analyze agent performance trends
        $excellentAgents = collect($agents)->where('performance', 'Excellent')->count();
        $needsImprovementAgents = collect($agents)->where('performance', 'Needs Improvement')->count();
        $totalAgents = count($agents);

        if ($needsImprovementAgents > $totalAgents * 0.3) {
            $insights[] = [
                'id' => 'high_underperformance',
                'type' => 'danger',
                'title' => 'High Agent Underperformance',
                'description' => "{$needsImprovementAgents} out of {$totalAgents} agents need improvement. This may indicate training gaps or process issues.",
                'action' => 'Schedule training sessions and review current processes with underperforming agents.'
            ];
        }

        if ($excellentAgents > $totalAgents * 0.4) {
            $insights[] = [
                'id' => 'strong_performance',
                'type' => 'success',
                'title' => 'Strong Team Performance',
                'description' => "{$excellentAgents} out of {$totalAgents} agents are performing excellently. Your team is showing great results.",
                'action' => 'Consider promoting top performers and having them mentor others.'
            ];
        }

        // Check activity patterns
        $lowActivityAgents = collect($agents)->where('totalActions', '<', 20)->count();
        if ($lowActivityAgents > 0) {
            $insights[] = [
                'id' => 'low_activity',
                'type' => 'warning',
                'title' => 'Low Activity Detected',
                'description' => "{$lowActivityAgents} agents have low activity levels, which may indicate disengagement or workload issues.",
                'action' => 'Review workload distribution and engage with low-activity agents to understand challenges.'
            ];
        }

        // Order creation vs updates ratio
        $totalOrdersCreated = collect($agents)->sum('ordersCreated');
        $totalOrdersUpdated = collect($agents)->sum('ordersUpdated');
        
        if ($totalOrdersCreated > 0 && $totalOrdersUpdated / $totalOrdersCreated < 0.5) {
            $insights[] = [
                'id' => 'low_follow_up',
                'type' => 'warning',
                'title' => 'Low Order Follow-up Rate',
                'description' => 'Order updates are significantly lower than order creation, suggesting poor follow-up processes.',
                'action' => 'Implement better order tracking and follow-up procedures.'
            ];
        }

        // If no specific insights, provide general advice
        if (empty($insights)) {
            $insights[] = [
                'id' => 'general_advice',
                'type' => 'info',
                'title' => 'Overall Performance Stable',
                'description' => 'Your team performance is stable. Consider setting new challenges to drive growth.',
                'action' => 'Set performance goals and introduce gamification to boost engagement.'
            ];
        }

        return $insights;
    }

    private function getActivityTimeline($dateRange)
    {
        [$from, $to] = $dateRange;
        
        $days = min($from->diffInDays($to) + 1, 30); // Limit to 30 days max
        $timeline = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            $count = ActionHistory::whereDate('created_at', $date)->count();
            
            $timeline[] = [
                'date' => $date->format('M j'),
                'count' => $count
            ];
        }

        // If no data, provide sample data to show the chart structure
        if (empty($timeline) || collect($timeline)->sum('count') === 0) {
            $timeline = [
                ['date' => 'Sep 17', 'count' => 0],
                ['date' => 'Sep 18', 'count' => 0],
                ['date' => 'Sep 19', 'count' => 0],
                ['date' => 'Sep 20', 'count' => 0],
                ['date' => 'Sep 21', 'count' => 0],
                ['date' => 'Sep 22', 'count' => 3],
                ['date' => 'Sep 23', 'count' => 2],
            ];
        }

        return $timeline;
    }

    private function getActionTypes($dateRange)
    {
        [$from, $to] = $dateRange;

        $actionTypes = ActionHistory::whereBetween('created_at', [$from, $to])
            ->select('title', DB::raw('COUNT(*) as count'))
            ->groupBy('title')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->title,
                    'count' => $item->count
                ];
            });

        // If no data, provide sample data to show the chart structure
        if ($actionTypes->isEmpty()) {
            $actionTypes = collect([
                ['title' => 'Product Updated', 'count' => 3],
                ['title' => 'Order Created', 'count' => 1],
                ['title' => 'Orders Assigned', 'count' => 1],
            ]);
        }

        return $actionTypes->toArray();
    }

    public function availableAgents(Request $request)
    {
        // Check if user is authenticated and is superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            return response()->json(['message' => 'Unauthorized access. Superadmin role required.'], 403);
        }

        // Get all users with 'agent' role
        $agents = User::whereHas('roles', function($query) {
            $query->where('name', 'agent');
        })->select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'agents' => $agents
        ]);
    }

    public function agentAnalysis(Request $request)
    {
        // Check if user is authenticated and is superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            return response()->json(['message' => 'Unauthorized access. Superadmin role required.'], 403);
        }

        $agentId = $request->input('agent_id');
        $period = $request->input('period', 'last_7_days');
        
        if (!$agentId) {
            return response()->json(['message' => 'Agent ID is required'], 400);
        }

        // Verify the user is an agent
        $agent = User::whereHas('roles', function($query) {
            $query->where('name', 'agent');
        })->find($agentId);

        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        $dateRange = $this->getDateRange($period, $request);
        $analysis = $this->buildAgentAnalysis($agent, $dateRange);

        return response()->json($analysis);
    }

    private function buildAgentAnalysis($agent, $dateRange)
    {
        [$from, $to] = $dateRange;

        // Get all actions by this agent in the date range
        $actions = ActionHistory::where('user_id', $agent->id)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        // Overview metrics
        $overview = $this->calculateAgentOverview($agent, $actions, $dateRange);
        
        // Daily activity
        $dailyActivity = $this->calculateDailyActivity($actions, $dateRange);
        
        // Performance metrics breakdown
        $performanceMetrics = $this->calculatePerformanceMetrics($actions);
        
        // Response time trend
        $responseTrend = $this->calculateResponseTrend($agent, $dateRange);
        
        // Assessment
        $assessment = $this->generateAgentAssessment($agent, $actions, $overview);

        return [
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name
            ],
            'overview' => $overview,
            'daily_activity' => $dailyActivity,
            'performance_metrics' => $performanceMetrics,
            'response_trend' => $responseTrend,
            'assessment' => $assessment
        ];
    }

    private function calculateAgentOverview($agent, $actions, $dateRange)
    {
        [$from, $to] = $dateRange;
        
        $totalActions = $actions->count();
        
        // Count orders handled (orders the agent has acted upon)
        $ordersHandled = $actions->filter(function($action) {
            return str_contains($action->title, 'Order');
        })->unique(function($action) {
            return $action->details['order_id'] ?? null;
        })->count();

        // Calculate average response time
        $orderActions = $actions->filter(function($action) {
            return str_contains($action->title, 'Order') && isset($action->details['order_id']);
        });

        $responseTimes = [];
        foreach ($orderActions->groupBy('details.order_id') as $orderActions) {
            $firstAction = $orderActions->first();
            if ($firstAction) {
                $order = Order::find($firstAction->details['order_id']);
                if ($order) {
                    $responseHours = $order->created_at->diffInHours($firstAction->created_at);
                    $responseTimes[] = $responseHours;
                }
            }
        }

        $avgResponseTime = empty($responseTimes) ? 0 : array_sum($responseTimes) / count($responseTimes);
        $avgResponseTimeFormatted = $avgResponseTime < 1 ? round($avgResponseTime * 60) . 'min' : round($avgResponseTime, 1) . 'h';

        // Calculate success rate
        $positiveActions = $actions->filter(function($action) {
            return str_contains($action->title, 'Created') || 
                   str_contains($action->title, 'Confirmed') ||
                   str_contains($action->title, 'Completed');
        })->count();

        $successRate = $totalActions > 0 ? round(($positiveActions / $totalActions) * 100) : 0;

        return [
            'total_actions' => $totalActions,
            'orders_handled' => $ordersHandled,
            'avg_response_time' => $avgResponseTimeFormatted,
            'success_rate' => $successRate
        ];
    }

    private function calculateDailyActivity($actions, $dateRange)
    {
        [$from, $to] = $dateRange;
        
        $days = min($from->diffInDays($to) + 1, 30);
        $activity = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            $dayActions = $actions->filter(function($action) use ($date) {
                return $action->created_at->isSameDay($date);
            })->count();
            
            $activity[] = [
                'date' => $date->format('M j'),
                'actions' => $dayActions
            ];
        }

        return $activity;
    }

    private function calculatePerformanceMetrics($actions)
    {
        $metrics = [
            'Order Created' => 0,
            'Order Updated' => 0,
            'Status Changed' => 0,
            'Other Actions' => 0
        ];

        foreach ($actions as $action) {
            if (str_contains($action->title, 'Order Created')) {
                $metrics['Order Created']++;
            } elseif (str_contains($action->title, 'Order') && str_contains($action->title, 'Status')) {
                $metrics['Status Changed']++;
            } elseif (str_contains($action->title, 'Order')) {
                $metrics['Order Updated']++;
            } else {
                $metrics['Other Actions']++;
            }
        }

        return collect($metrics)->map(function($value, $key) {
            return ['label' => $key, 'value' => $value];
        })->values()->toArray();
    }

    private function calculateResponseTrend($agent, $dateRange)
    {
        [$from, $to] = $dateRange;
        
        $days = min($from->diffInDays($to) + 1, 30);
        $trend = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            
            // Get order actions for this day
            $dayOrderActions = ActionHistory::where('user_id', $agent->id)
                ->whereDate('created_at', $date)
                ->where('title', 'like', '%Order%')
                ->whereNotNull('details->order_id')
                ->get();

            $dayResponseTimes = [];
            foreach ($dayOrderActions as $action) {
                $order = Order::find($action->details['order_id']);
                if ($order) {
                    $responseHours = $order->created_at->diffInHours($action->created_at);
                    $dayResponseTimes[] = $responseHours;
                }
            }

            $avgResponseHours = empty($dayResponseTimes) ? 0 : array_sum($dayResponseTimes) / count($dayResponseTimes);
            
            $trend[] = [
                'date' => $date->format('M j'),
                'avg_hours' => round($avgResponseHours, 1)
            ];
        }

        return $trend;
    }

    private function generateAgentAssessment($agent, $actions, $overview)
    {
        $strengths = [];
        $improvements = [];
        $verdict = '';
        $recommendation = '';

        // Analyze performance
        $totalActions = $overview['total_actions'];
        $successRate = $overview['success_rate'];
        $ordersHandled = $overview['orders_handled'];

        // Determine strengths
        if ($totalActions >= 30) {
            $strengths[] = 'High activity level with ' . $totalActions . ' total actions';
        }
        if ($successRate >= 80) {
            $strengths[] = 'Excellent success rate at ' . $successRate . '%';
        }
        if ($ordersHandled >= 15) {
            $strengths[] = 'Strong order handling capacity with ' . $ordersHandled . ' orders managed';
        }
        if (str_contains($overview['avg_response_time'], 'min') || (str_contains($overview['avg_response_time'], 'h') && floatval($overview['avg_response_time']) <= 2)) {
            $strengths[] = 'Quick response time averaging ' . $overview['avg_response_time'];
        }

        // Determine improvements
        if ($totalActions < 15) {
            $improvements[] = 'Activity level could be increased (currently ' . $totalActions . ' actions)';
        }
        if ($successRate < 70) {
            $improvements[] = 'Success rate needs improvement (currently ' . $successRate . '%)';
        }
        if ($ordersHandled < 5) {
            $improvements[] = 'Order handling experience could be expanded';
        }
        if (str_contains($overview['avg_response_time'], 'h') && floatval($overview['avg_response_time']) > 3) {
            $improvements[] = 'Response time could be faster (currently ' . $overview['avg_response_time'] . ')';
        }

        // Generate verdict and recommendation
        if ($totalActions >= 25 && $successRate >= 75 && $ordersHandled >= 10) {
            $verdict = 'Keep - High Performer';
            $recommendation = 'Excellent agent with strong performance metrics. Consider for advanced responsibilities or mentoring new agents.';
        } elseif ($totalActions >= 15 && $successRate >= 60 && $ordersHandled >= 5) {
            $verdict = 'Keep - Good Performer';
            $recommendation = 'Solid performer with room for growth. Provide additional training and support to reach high performer status.';
        } elseif ($totalActions >= 10 || $successRate >= 50) {
            $verdict = 'Improve - Needs Development';
            $recommendation = 'Shows potential but needs focused training and monitoring. Set clear performance goals and provide regular feedback.';
        } else {
            $verdict = 'Consider Replacement';
            $recommendation = 'Performance below expectations. Consider providing intensive training or evaluating fit for the role.';
        }

        // Ensure we have at least some feedback
        if (empty($strengths)) {
            $strengths[] = 'Shows willingness to engage with the system';
        }
        if (empty($improvements)) {
            $improvements[] = 'Continue maintaining current performance standards';
        }

        return [
            'strengths' => $strengths,
            'improvements' => $improvements,
            'recommendation' => $recommendation,
            'verdict' => $verdict
        ];
    }
}
