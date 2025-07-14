<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['OPENAI_API_KEY'];
$organization = $_ENV['OPENAI_ORGANIZATION'] ?? null;
$projectId = $_ENV['OPENAI_PROJECT_ID'] ?? null;

echo "Testing OpenAI API connection...\n";
echo "API Key: " . substr($apiKey, 0, 20) . "...\n";
echo "Organization: " . $organization . "\n";
echo "Project ID: " . $projectId . "\n\n";

// Test the models endpoint
$url = 'https://api.openai.com/v1/models';
$headers = [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
];

if ($organization) {
    $headers[] = 'OpenAI-Organization: ' . $organization;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";

if ($error) {
    echo "cURL Error: " . $error . "\n";
} else {
    $data = json_decode($response, true);
    if ($httpCode === 200) {
        echo "✅ API Connection Successful!\n";
        echo "Available models: " . count($data['data']) . "\n";
        foreach (array_slice($data['data'], 0, 5) as $model) {
            echo "- " . $model['id'] . "\n";
        }
    } else {
        echo "❌ API Error: " . $httpCode . "\n";
        echo "Response: " . $response . "\n";
    }
}

echo "\nTesting chat completion...\n";

// Test a simple chat completion
$chatUrl = 'https://api.openai.com/v1/chat/completions';
$chatData = [
    'model' => 'gpt-4',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a helpful assistant.'
        ],
        [
            'role' => 'user',
            'content' => 'Hello! Can you help me with business questions?'
        ]
    ],
    'max_tokens' => 100,
    'temperature' => 0.7
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $chatUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($chatData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$chatResponse = curl_exec($ch);
$chatHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$chatError = curl_error($ch);
curl_close($ch);

echo "Chat HTTP Code: " . $chatHttpCode . "\n";

if ($chatError) {
    echo "cURL Error: " . $chatError . "\n";
} else {
    $chatData = json_decode($chatResponse, true);
    if ($chatHttpCode === 200) {
        echo "✅ Chat Completion Successful!\n";
        echo "Response: " . $chatData['choices'][0]['message']['content'] . "\n";
    } else {
        echo "❌ Chat Error: " . $chatHttpCode . "\n";
        echo "Response: " . $chatResponse . "\n";
    }
} 