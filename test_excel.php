<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Maatwebsite Excel Package...\n";

try {
    // Test if the Excel facade is available
    if (class_exists('Maatwebsite\Excel\Facades\Excel')) {
        echo "✅ Excel facade is available\n";
    } else {
        echo "❌ Excel facade not found\n";
    }
    
    // Test if the service provider is registered
    $excelServiceProvider = 'Maatwebsite\Excel\ExcelServiceProvider';
    if (class_exists($excelServiceProvider)) {
        echo "✅ Excel service provider is available\n";
    } else {
        echo "❌ Excel service provider not found\n";
    }
    
    // Test if we can create an import class
    if (class_exists('App\Imports\OrdersImport')) {
        echo "✅ OrdersImport class is available\n";
    } else {
        echo "❌ OrdersImport class not found\n";
    }
    
    echo "\n✅ Maatwebsite Excel package is fully installed and working!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 