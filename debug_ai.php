<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\AiChatController;
use Illuminate\Http\Request;

echo "Testing AI Chat Controller...\n\n";

try {
    // Create a test request
    $request = new Request();
    $request->merge(['message' => 'Hello, can you help me with business questions?']);
    
    // Create controller instance
    $controller = new AiChatController();
    
    echo "✅ Controller created successfully\n";
    
    // Test the chat method
    $response = $controller->chat($request);
    
    echo "✅ Response received\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    
    $responseData = $response->getData();
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Also check Laravel logs
echo "\nChecking Laravel logs...\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLogs = array_slice($lines, -20);
    echo "Recent logs:\n";
    foreach ($recentLogs as $line) {
        if (trim($line) !== '') {
            echo $line . "\n";
        }
    }
} 