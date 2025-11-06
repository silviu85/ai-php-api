<?php
// File: app/services/Ai/GeminiService.php

namespace App\Services\Ai;

use App\Data\AiSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;

class GeminiService implements AiServiceInterface
{
    protected string $apiKey;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

  public function ask(array $messages, AiSettings $settings): string
{
    // Aici re-introducem logica ta originală, funcțională.
    $geminiContents = [];
    $systemContent = $settings->systemPrompt;

    // Căutăm rezumatul din conversație
    foreach ($messages as $message) {
        if ($message['role'] === 'system') {
            $systemContent .= "\n\nConversation Summary: " . $message['content'];
            break;
        }
    }

    $isFirstUserMessage = true;
    foreach ($messages as $message) {
        if (in_array($message['role'], ['user', 'assistant'])) {
            $role = ($message['role'] === 'assistant') ? 'model' : 'user';
            $content = $message['content'];

            // Injecting System prompt in user first message
            if ($systemContent && $role === 'user' && $isFirstUserMessage) {
                $content = "IMPORTANT INSTRUCTIONS: '{$systemContent}'.\n\n--- My actual prompt ---\n{$content}";
                $isFirstUserMessage = false;
            }

             $geminiContents[] = [
                'role' => $role,
                'parts' => [['text' => $content]]
            ];
        }
    }

    // Building payload for API
    try {
        $response = Http::timeout(60)->post($this->apiUrl . '?key=' . $this->apiKey, [
            'contents' => $geminiContents,
            // Use settings from standardised object $settings
            'generation_config' => [
                'temperature' => $settings->temperature,
                'maxOutputTokens' => $settings->maxTokens,
            ],
        ]);

        $response->throw();
        
        return Arr::get($response->json(), 'candidates.0.content.parts.0.text') ?? 'Sorry, I could not get a response from Gemini.';

    } catch (RequestException $e) {
        report($e);
        return 'Error communicating with the Gemini API: ' . $e->getMessage();
    }
}
}
