<?php
// File: app/services/Ai/ChatGptService.php

namespace App\Services\Ai;

use App\Data\AiSettings;
use OpenAI\Client;
use OpenAI;

class ChatGptService implements AiServiceInterface
{
    protected Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Implements the ask method for ChatGPT.
     * This method now correctly accepts the AiSettings object.
     */
    public function ask(array $messages, AiSettings $settings): string
    {
        $payload = [
            'model' => 'gpt-4o',
            'messages' => $this->prepareMessages($messages, $settings),
            'temperature' => $settings->temperature,
            'max_tokens' => $settings->maxTokens,
        ];

        $response = $this->client->chat()->create($payload);

        return $response->choices[0]->message->content ?? 'Error from ChatGPT.';
    }

    private function prepareMessages(array $messages, AiSettings $settings): array
    {
        $preparedMessages = [];
        $systemPromptFound = false;

        if ($settings->systemPrompt) {
            $preparedMessages[] = ['role' => 'system', 'content' => $settings->systemPrompt];
            $systemPromptFound = true;
        }

        foreach ($messages as $message) {
            if ($message['role'] === 'system' && !$systemPromptFound) {
                $preparedMessages[] = $message;
                $systemPromptFound = true;
            } elseif (in_array($message['role'], ['user', 'assistant'])) {
                $preparedMessages[] = $message;
            }
        }
        return $preparedMessages;
    }
}
