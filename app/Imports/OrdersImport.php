<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderStatus;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class OrdersImport extends StringValueBinder implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsEmptyRows, WithCustomCsvSettings, WithCustomValueBinder
{
    public $successCount = 0;
    public $errorCount = 0;
    public $errors = [];
    public $defaultOrderStatus;

    public function __construct()
    {
        // Prefer 'New Order' as default status, fall back to 'Pending', then 'Confirmed', then first available
        $this->defaultOrderStatus = OrderStatus::where('name', 'New Order')->first()
            ?? OrderStatus::where('name', 'Pending')->first()
            ?? OrderStatus::where('name', 'Confirmed')->first()
            ?? OrderStatus::first();
    }

    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous_delimiter' => false,
            'use_bom' => false,
        ];
    }

    public function model(array $row)
    {
        try {
            // Ensure all string fields are properly cast to strings to prevent type conversion issues
            $row = $this->castRowToStrings($row);
            
            // Validate required fields
            if (empty($row['product_sku']) || empty($row['seller_username']) || 
                empty($row['client_name']) || empty($row['client_phone']) || 
                empty($row['client_address']) || empty($row['quantity']) || 
                empty($row['price'])) {
                
                $this->errorCount++;
                $this->errors[] = "Row " . ($this->errorCount + $this->successCount + 1) . ": Missing required fields";
                return null;
            }

            // Find product by SKU
            $product = Product::where('sku', trim($row['product_sku']))->first();
            if (!$product) {
                $this->errorCount++;
                $this->errors[] = "Row " . ($this->errorCount + $this->successCount + 1) . ": Product with SKU '{$row['product_sku']}' not found";
                return null;
            }

            // Find seller by username (assuming username is email or name)
            $seller = User::where('email', trim($row['seller_username']))
                ->orWhere('name', trim($row['seller_username']))
                ->first();
            
            if (!$seller) {
                $this->errorCount++;
                $this->errors[] = "Row " . ($this->errorCount + $this->successCount + 1) . ": Seller '{$row['seller_username']}' not found";
                return null;
            }

            // Validate quantity and price
            $quantity = (int) $row['quantity'];
            $price = (float) $row['price'];
            
            if ($quantity <= 0) {
                $this->errorCount++;
                $this->errors[] = "Row " . ($this->errorCount + $this->successCount + 1) . ": Invalid quantity '{$row['quantity']}'";
                return null;
            }

            if ($price <= 0) {
                $this->errorCount++;
                $this->errors[] = "Row " . ($this->errorCount + $this->successCount + 1) . ": Invalid price '{$row['price']}'";
                return null;
            }

            // Create the order
            $order = new Order([
                'seller' => $seller->name,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'client_name' => trim($row['client_name']),
                'client_phone' => trim($row['client_phone']),
                'client_address' => trim($row['client_address']),
                'price' => $price,
                'order_status_id' => $this->defaultOrderStatus->id,
                'zone' => null,
                'notes' => $row['notes'] ?? null,
            ]);

            $this->successCount++;
            return $order;

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errors[] = "Row " . ($this->errorCount + $this->successCount + 1) . ": " . $e->getMessage();
            Log::error('Order import error: ' . $e->getMessage(), ['row' => $row]);
            return null;
        }
    }

    /**
     * Cast specific fields to strings to prevent automatic type conversion issues
     */
    private function castRowToStrings(array $row): array
    {
        // List of fields that should always be treated as strings
        $stringFields = [
            'product_sku',
            'seller_username', 
            'client_name',
            'client_phone',
            'client_address',
            'notes'
        ];

        foreach ($stringFields as $field) {
            if (isset($row[$field]) && $row[$field] !== null) {
                $row[$field] = (string) $row[$field];
            }
        }

        return $row;
    }

    public function rules(): array
    {
        return [
            'product_sku' => 'required|string',
            'seller_username' => 'required|string',
            'client_name' => 'required|string',
            'client_phone' => 'required|string',
            'client_address' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'product_sku.required' => 'Product SKU is required',
            'seller_username.required' => 'Seller username is required',
            'client_name.required' => 'Client name is required',
            'client_phone.required' => 'Client phone is required',
            'client_address.required' => 'Client address is required',
            'quantity.required' => 'Quantity is required',
            'quantity.numeric' => 'Quantity must be a number',
            'quantity.min' => 'Quantity must be at least 1',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price must be at least 0',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errorCount++;
        $this->errors[] = "Error: " . $e->getMessage();
        Log::error('Order import error: ' . $e->getMessage());
    }

    public function getResults()
    {
        return [
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
        ];
    }
} 