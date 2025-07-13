<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\User;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        // Validate the request
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {
            // Get the user's message
            $userMessage = $request->input('message');
            
            // Check if OpenAI API key is configured
            $openaiApiKey = env('OPENAI_API_KEY');
            if (!$openaiApiKey) {
                return response()->json([
                    'error' => 'OpenAI API key not configured'
                ], 500);
            }

            // Gather relevant data based on the user's question
            $contextData = $this->gatherContextData($userMessage);
            
            // Build the prompt for OpenAI
            $prompt = $this->buildPrompt($userMessage, $contextData);
            
            // Call OpenAI API
            $response = $this->callOpenAI($prompt, $openaiApiKey);
            
            return response()->json([
                'response' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    private function gatherContextData($userMessage)
    {
        $data = [];
        $message = strtolower($userMessage);
        
        // Check for order-related queries
        if (str_contains($message, 'order') || str_contains($message, 'client') || str_contains($message, 'customer')) {
            $data['orders'] = $this->getOrdersData();
        }
        
        // Check for financial queries
        if (str_contains($message, 'income') || str_contains($message, 'expense') || str_contains($message, 'cash') || str_contains($message, 'money') || str_contains($message, 'total')) {
            $data['finances'] = $this->getFinancialData();
        }
        
        // Check for product queries
        if (str_contains($message, 'product') || str_contains($message, 'item') || str_contains($message, 'catalog')) {
            $data['products'] = $this->getProductsData();
        }
        
        // Check for user/agent queries
        if (str_contains($message, 'agent') || str_contains($message, 'user') || str_contains($message, 'employee')) {
            $data['users'] = $this->getUsersData();
        }
        
        // Always include basic stats
        $data['basic_stats'] = $this->getBasicStats();
        
        return $data;
    }

    private function getOrdersData()
    {
        $orders = Order::with(['product', 'orderStatus'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return [
            'total_orders' => Order::count(),
            'recent_orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'client_name' => $order->client_name,
                    'product_name' => $order->product->name ?? 'Unknown',
                    'quantity' => $order->quantity,
                    'price' => $order->price,
                    'total_price' => $order->total_price,
                    'status' => $order->orderStatus->name ?? 'Unknown',
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'agent' => $order->agent
                ];
            }),
            'orders_by_status' => Order::with('orderStatus')
                ->get()
                ->groupBy('orderStatus.name')
                ->map(function ($group) {
                    return $group->count();
                }),
            'orders_by_month' => Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get()
                ->pluck('count', 'month')
        ];
    }

    private function getFinancialData()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        return [
            'incomes' => [
                'total' => Income::sum('amount'),
                'this_month' => Income::whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->sum('amount'),
                'recent' => Income::with('category')
                    ->orderBy('date', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($income) {
                        return [
                            'amount' => $income->amount,
                            'category' => $income->category->name ?? 'Uncategorized',
                            'date' => $income->date->format('Y-m-d'),
                            'description' => $income->description
                        ];
                    })
            ],
            'expenses' => [
                'total' => Expense::sum('amount'),
                'this_month' => Expense::whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->sum('amount'),
                'recent' => Expense::with('category')
                    ->orderBy('date', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($expense) {
                        return [
                            'amount' => $expense->amount,
                            'category' => $expense->category->name ?? 'Uncategorized',
                            'date' => $expense->date->format('Y-m-d'),
                            'description' => $expense->description
                        ];
                    })
            ],
            'cash_on_delivery_today' => Order::whereDate('created_at', Carbon::today())
                ->sum('price')
        ];
    }

    private function getProductsData()
    {
        return [
            'total_products' => Product::count(),
            'products' => Product::with('category')
                ->orderBy('name')
                ->limit(20)
                ->get()
                ->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'price' => $product->price,
                        'category' => $product->category->name ?? 'Uncategorized',
                        'description' => $product->description
                    ];
                }),
            'categories' => Category::withCount('products')
                ->get()
                ->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'product_count' => $category->products_count
                    ];
                })
        ];
    }

    private function getUsersData()
    {
        return [
            'total_users' => User::count(),
            'users_by_role' => User::with('roles')
                ->get()
                ->groupBy(function ($user) {
                    return $user->roles->first()->name ?? 'No Role';
                })
                ->map(function ($group) {
                    return $group->count();
                }),
            'agents' => User::with('roles')
                ->get()
                ->filter(function ($user) {
                    return $user->hasRole('agent');
                })
                ->map(function ($user) {
                    return [
                        'name' => $user->name,
                        'email' => $user->email
                    ];
                })
        ];
    }

    private function getBasicStats()
    {
        return [
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_users' => User::count(),
            'total_income' => Income::sum('amount'),
            'total_expenses' => Expense::sum('amount'),
            'orders_today' => Order::whereDate('created_at', Carbon::today())->count(),
            'income_today' => Income::whereDate('date', Carbon::today())->sum('amount'),
            'expenses_today' => Expense::whereDate('date', Carbon::today())->sum('amount')
        ];
    }

    private function buildPrompt($userMessage, $contextData)
    {
        $context = json_encode($contextData, JSON_PRETTY_PRINT);
        
        return "You are an AI assistant for a business management system. You have access to the following data about the business:

{$context}

User Question: {$userMessage}

Please provide a helpful, accurate, and concise response based on the available data. If the user is asking about specific information that's not in the data, politely let them know what information is available. 

Guidelines:
- Be professional and friendly
- Provide specific numbers and facts when available
- If asked about trends, analyze the data patterns
- Keep responses concise but informative
- If the data shows no results for a query, clearly state that
- Format currency values properly
- Use bullet points for lists when appropriate

Response:";
    }

    private function callOpenAI($prompt, $apiKey)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful AI assistant for a business management system. Provide accurate, concise, and professional responses based on the data provided.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';
        } else {
            Log::error('OpenAI API Error: ' . $response->body());
            throw new \Exception('Failed to get response from OpenAI API');
        }
    }
} 