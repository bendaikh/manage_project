<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AiChatController;
use Illuminate\Http\Request;

class TestAiChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:test {message : The message to send to the AI}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the AI chatbot functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = $this->argument('message');
        
        $this->info("Testing AI Chat with message: {$message}");
        $this->newLine();
        
        // Check if OpenAI API key is configured
        if (!env('OPENAI_API_KEY')) {
            $this->error('OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file.');
            return 1;
        }
        
        try {
            // Create a mock request
            $request = new Request();
            $request->merge(['message' => $message]);
            
            // Create controller instance
            $controller = new AiChatController();
            
            // Get the response
            $response = $controller->chat($request);
            $responseData = $response->getData();
            
            if ($response->getStatusCode() === 200) {
                $this->info('✅ AI Response:');
                $this->line($responseData->response);
            } else {
                $this->error('❌ Error: ' . ($responseData->error ?? 'Unknown error'));
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 