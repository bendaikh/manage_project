<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\OrdersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OrderImportController extends Controller
{
    public function import(Request $request)
    {
        try {
            // Validate the uploaded file
            $request->validate([
                'orders_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            ]);

            // Store the uploaded file temporarily
            $file = $request->file('orders_file');
            $fileName = 'orders_import_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('temp', $fileName);

            // Import the orders
            $import = new OrdersImport();
            Excel::import($import, $filePath);

            // Get results
            $results = $import->getResults();

            // Clean up temporary file
            Storage::delete($filePath);

            // Log the import results
            Log::info('Orders import completed', $results);

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Order import failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'results' => [
                    'success_count' => 0,
                    'error_count' => 1,
                    'errors' => [$e->getMessage()],
                ],
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        // Create a sample CSV template
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders_import_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'product_sku',
                'seller_username', 
                'client_name',
                'client_phone',
                'client_address',
                'quantity',
                'price',
                'notes'
            ]);

            // Add sample data
            fputcsv($file, [
                'SKU001',
                'seller@example.com',
                'John Doe',
                '+1234567890',
                '123 Main St, City, Country',
                '2',
                '5000',
                'Sample order notes'
            ]);

            fputcsv($file, [
                'SKU002',
                'seller@example.com',
                'Jane Smith',
                '+0987654321',
                '456 Oak Ave, Town, Country',
                '1',
                '3000',
                'Another sample order'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getImportStats()
    {
        // Get some statistics for the import form
        $stats = [
            'total_products' => \App\Models\Product::count(),
            'total_sellers' => \App\Models\User::count(),
            'total_orders' => \App\Models\Order::count(),
            'order_statuses' => \App\Models\OrderStatus::pluck('name'),
        ];

        return response()->json($stats);
    }
} 