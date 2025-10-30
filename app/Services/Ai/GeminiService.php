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
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-latest:generateContent';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function ask(array $messages, AiSettings $settings): string
    {
        // We rely entirely on the preparePayload method for logic.
        list($systemInstruction, $geminiContents) = $this->preparePayload($messages, $settings);

        $payload = [
            'contents' => $geminiContents,
            'generation_config' => [
                'temperature' => $settings->temperature,
                'maxOutputTokens' => $settings->maxTokens,
            ],
        ];

        if ($systemInstruction) {
            $payload['system_instruction'] = $systemInstruction;
        }

        try {
            $response = Http::timeout(60)->post($this->apiUrl . '?key=' . $this->apiKey, $payload);
            $response->throw();
            return Arr::get($response->json(), 'candidates.0.content.parts.0.text') ?? 'Sorry, I could not get a response from Gemini.';
        } catch (RequestException $e) {
            report($e);
            return 'Error communicating with the Gemini API: ' . $e->getMessage();
        }
    }

    private function preparePayload(array $messages, AiSettings $settings): array
    {
        $geminiContents = [];
        $systemContent = $settings->systemPrompt;

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemContent = $settings->systemPrompt . "\n\nConversation Summary: " . $message['content'];
                break;
            }
        }

        $systemInstruction = $systemContent ? ['role' => 'user', 'parts' => [['text' => $systemContent]]] : null;

        foreach ($messages as $message) {
            if (in_array($message['role'], ['user', 'assistant'])) {
                $geminiContents[] = [
                    'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $message['content']]]
                ];
            }
        }

        return [$systemInstruction, $geminiContents];
    }
}
