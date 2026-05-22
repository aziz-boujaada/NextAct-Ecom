<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiChat\AipromptsService;
use App\Services\AiChat\ChatService;
use Illuminate\Http\Request;
use GuzzleHttp\Client;


class AiChatController extends Controller
{


    public function __construct(private AipromptsService $aipromptsService) {}
    public function chat(Request $request, ChatService $chatService)
    {
        $message = $request->input('message');
        $data = $chatService->Aichat($message);
        $answer = $this->callAI($message, $data);

        return response()->json([
            'answer' => $answer,
        ]);
    }

    private function callAI(string $message,array $data)
    {
        $client = new Client();
        $prompt = $this->aipromptsService->generatePrompt($data, $message);

        $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an ERP assistant. Use only provided data.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.2,
                'max_tokens' => 500
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['choices'][0]['message']['content'] ?? null;
    }
}
