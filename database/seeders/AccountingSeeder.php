<?php

namespace Database\Seeders;

use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use App\Models\Account;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Transfer;
use App\Models\Refund;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Income Categories
        $incomeCategories = [
            ['name' => 'Sales', 'description' => 'Revenue from product sales', 'color' => '#10B981'],
            ['name' => 'Services', 'description' => 'Revenue from services provided', 'color' => '#3B82F6'],
            ['name' => 'Investments', 'description' => 'Returns from investments', 'color' => '#8B5CF6'],
            ['name' => 'Consulting', 'description' => 'Consulting fees', 'color' => '#F59E0B'],
            ['name' => 'Other', 'description' => 'Other income sources', 'color' => '#6B7280'],
        ];

        foreach ($incomeCategories as $category) {
            IncomeCategory::create($category);
        }

        // Create Expense Categories
        $expenseCategories = [
            ['name' => 'Rent', 'description' => 'Office and equipment rent', 'color' => '#EF4444'],
            ['name' => 'Salaries', 'description' => 'Employee salaries and wages', 'color' => '#F97316'],
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet, etc.', 'color' => '#EC4899'],
            ['name' => 'Marketing', 'description' => 'Advertising and marketing expenses', 'color' => '#06B6D4'],
            ['name' => 'Supplies', 'description' => 'Office supplies and materials', 'color' => '#84CC16'],
            ['name' => 'Travel', 'description' => 'Business travel expenses', 'color' => '#A855F7'],
            ['name' => 'Other', 'description' => 'Other expenses', 'color' => '#6B7280'],
        ];

        foreach ($expenseCategories as $category) {
            ExpenseCategory::create($category);
        }

        // Create Accounts
        $accounts = [
            [
                'name' => 'Main Bank Account',
                'description' => 'Primary business bank account',
                'initial_balance' => 50000,
                'current_balance' => 50000,
                'type' => 'Bank',
                'status' => 'Active',
            ],
            [
                'name' => 'Savings Account',
                'description' => 'Business savings account',
                'initial_balance' => 25000,
                'current_balance' => 25000,
                'type' => 'Bank',
                'status' => 'Active',
            ],
            [
                'name' => 'Petty Cash',
                'description' => 'Petty cash fund',
                'initial_balance' => 1000,
                'current_balance' => 1000,
                'type' => 'Cash',
                'status' => 'Active',
            ],
            [
                'name' => 'Investment Account',
                'description' => 'Investment portfolio',
                'initial_balance' => 75000,
                'current_balance' => 75000,
                'type' => 'Investment',
                'status' => 'Active',
            ],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }

        // Create sample incomes
        $this->createSampleIncomes();
        
        // Create sample expenses
        $this->createSampleExpenses();
        
        // Create sample transfers
        $this->createSampleTransfers();
        
        // Create sample refunds
        $this->createSampleRefunds();
    }

    private function createSampleIncomes()
    {
        $categories = IncomeCategory::all();
        $users = \App\Models\User::all();
        $accounts = Account::all();

        for ($i = 0; $i < 20; $i++) {
            Income::create([
                'title' => 'Sample Income ' . ($i + 1),
                'amount' => rand(100, 5000),
                'date' => Carbon::now()->subDays(rand(0, 30)),
                'income_category_id' => $categories->random()->id,
                'account_id' => $accounts->random()->id,
                'description' => 'Sample income description',
                'user_id' => $users->random()->id,
            ]);
        }
    }

    private function createSampleExpenses()
    {
        $categories = ExpenseCategory::all();
        $users = \App\Models\User::all();
        $accounts = Account::all();

        for ($i = 0; $i < 15; $i++) {
            Expense::create([
                'title' => 'Sample Expense ' . ($i + 1),
                'amount' => rand(50, 2000),
                'date' => Carbon::now()->subDays(rand(0, 30)),
                'expense_category_id' => $categories->random()->id,
                'account_id' => $accounts->random()->id,
                'description' => 'Sample expense description',
                'refundable' => rand(0, 1),
                'user_id' => $users->random()->id,
            ]);
        }
    }

    private function createSampleTransfers()
    {
        $accounts = Account::all();
        $users = \App\Models\User::all();

        for ($i = 0; $i < 5; $i++) {
            $fromAccount = $accounts->random();
            $toAccount = $accounts->where('id', '!=', $fromAccount->id)->random();
            
            Transfer::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => rand(100, 1000),
                'date' => Carbon::now()->subDays(rand(0, 30)),
                'description' => 'Sample transfer',
                'user_id' => $users->random()->id,
            ]);
        }
    }

    private function createSampleRefunds()
    {
        $refundableExpenses = Expense::where('refundable', true)->get();
        $users = \App\Models\User::all();
        $accounts = Account::all();

        foreach ($refundableExpenses->take(3) as $expense) {
            Refund::create([
                'title' => 'Refund for ' . $expense->title,
                'amount' => $expense->amount * 0.5, // 50% refund
                'date' => Carbon::now()->subDays(rand(1, 10)),
                'expense_id' => $expense->id,
                'account_id' => $accounts->random()->id,
                'description' => 'Sample refund',
                'order_sku' => 'SKU-' . rand(1000, 9999),
                'reason' => 'Sample refund reason',
                'user_id' => $users->random()->id,
            ]);
        }
    }
} 