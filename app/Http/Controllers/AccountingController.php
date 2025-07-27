<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Transfer;
use App\Models\Account;
use Carbon\Carbon;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_accounting');
    }

    /**
     * Display the accounting dashboard.
     */
    public function index()
    {
        $currentMonth = Carbon::now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // Get monthly totals
        $monthlyIncome = Income::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount');
        $monthlyExpenses = Expense::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount');
        $monthlyRefunds = \App\Models\Refund::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount');
        $netIncome = $monthlyIncome - $monthlyExpenses + $monthlyRefunds;

        // Get recent transactions
        $recentIncomes = Income::with('category')->latest()->take(5)->get();
        $recentExpenses = Expense::with('category')->latest()->take(5)->get();
        $recentTransfers = Transfer::with(['fromAccount', 'toAccount'])->latest()->take(5)->get();

        // Get account balances
        $accounts = Account::where('is_active', true)->get();

        // Get yearly data for charts
        $yearlyData = $this->getYearlyData();

        return view('accounting.index', compact(
            'monthlyIncome',
            'monthlyExpenses',
            'monthlyRefunds',
            'netIncome',
            'recentIncomes',
            'recentExpenses',
            'recentTransfers',
            'accounts',
            'yearlyData'
        ));
    }

    public function balance()
    {
        $totalIncome = \App\Models\Income::sum('amount');
        $totalExpense = \App\Models\Expense::sum('amount');
        $balance = $totalIncome - $totalExpense;

        return view('accounting.balance', compact('totalIncome', 'totalExpense', 'balance'));
    }

    public function balanceData()
    {
        $totalIncome = Income::sum('amount');
        $totalExpense = Expense::sum('amount');
        return response()->json([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ]);
    }

    /**
     * Get yearly data for charts.
     */
    private function getYearlyData()
    {
        $currentYear = Carbon::now()->year;
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $startOfMonth = Carbon::create($currentYear, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::create($currentYear, $month, 1)->endOfMonth();

            $income = Income::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount');
            $expenses = Expense::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount');
            $refunds = \App\Models\Refund::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount');

            $data[] = [
                'month' => $startOfMonth->format('M'),
                'income' => $income,
                'expenses' => $expenses,
                'refunds' => $refunds,
                'net' => $income - $expenses + $refunds,
            ];
        }

        return $data;
    }
} 