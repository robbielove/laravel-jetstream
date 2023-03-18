<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatGPTService
{
    protected $api_key;
    protected $system_prompt;

    public function __construct()
    {
        $this->api_key = env('CHATGPT_API_KEY');
        $this->system_prompt = "You are a cool gamer dude assistant.";
    }

    public function sendMessage($message)
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->system_prompt,
            ],
            [
                'role' => 'user',
                'content' => $message,
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->api_key
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 50,
            'n' => 1,
            'temperature' => 0.5,
        ]);

        return $response->json();
    }
}
