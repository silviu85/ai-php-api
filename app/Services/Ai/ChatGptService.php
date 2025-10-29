<?php

namespace App\Services\Ai;

use App\Data\AiSettings;
use OpenAI;
use OpenAI\Client;

class ChatGptService implements AiServiceInterface
{
    protected Client $client;

    /**
     * The constructor receives the OpenAI API key.
     */
    public function __construct(string $apiKey)
    {
        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Implements the ask method for ChatGPT.
     */
public function ask(array $messages): string
{
        $payload = [
            'model' => 'gpt-4o', // This could also be a setting
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

        // Use the system prompt from the settings object first.
        if ($settings->systemPrompt) {
            $preparedMessages[] = ['role' => 'system', 'content' => $settings->systemPrompt];
            $systemPromptFound = true;
        }

        foreach ($messages as $message) {
            // If there's a system message from conversation summary,
            // and we haven't added the global one, add it.
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