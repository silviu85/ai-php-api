<?php
// File: app/Services/Ai/ClaudeAiService.php

namespace App\Services\Ai;

use App\Data\AiSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;

class ClaudeAiService implements AiServiceInterface
{
    /**
     * The API key for the Anthropic API.
     * @var string
     */
    protected string $apiKey;

    /**
     * The base URL for the Anthropic API.
     * @var string
     */
    protected string $apiUrl = 'https://api.anthropic.com/v1/messages';

    /**
     * The API version required by Anthropic.
     * @var string
     */
    protected string $apiVersion = '2023-06-01';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Implements the ask method for the Claude AI model.
     * This method acts as an adapter, translating our standard format
     * to the specific format required by the Anthropic API.
     */
    public function ask(array $messages, AiSettings $settings): string
    {

        $payload = [
            // Select a specific Claude model. 'claude-4-1-opus-20250805' is their most powerful model.
            'model' => 'claude-4-5-sonnet-20250929', // Sonnet is a good balance of speed and intelligence.
            'system' => $this->prepareSystemPrompt($messages, $settings),
            'messages' => $this->prepareMessages($messages),
            'temperature' => $settings->temperature,
            'max_tokens' => $settings->maxTokens,
        ];

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->apiVersion,
                'content-type' => 'application/json',
            ])
            ->timeout(60)
            ->post($this->apiUrl, array_filter($payload)); // array_filter removes null 'system' prompt

            $response->throw(); // Throw an exception for non-2xx responses.

            // The response structure for Claude is different.
            // The content is in an array, and we need the first block of type 'text'.
            $contentBlocks = Arr::get($response->json(), 'content', []);
            foreach ($contentBlocks as $block) {
                if ($block['type'] === 'text') {
                    return $block['text'];
                }
            }

            return 'Sorry, I could not extract a valid response from Claude.';

        } catch (RequestException $e) {
            report($e);
            // Try to get a more specific error message from the API response
            $errorData = $e->response->json('error.message', $e->getMessage());
            return 'Error communicating with the Claude API: ' . $errorData;
        }
    }

    /**
     * Extracts and prepares the system prompt for Claude.
     * Claude prefers a dedicated 'system' parameter.
     */
    private function prepareSystemPrompt(array $messages, AiSettings $settings): ?string
    {
        $systemContent = $settings->systemPrompt;

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $fullSystemPrompt = $settings->systemPrompt 
                    ? $settings->systemPrompt . "\n\n--- Conversation Summary ---\n" . $message['content']
                    : "--- Conversation Summary ---\n" . $message['content'];
                $systemContent = $fullSystemPrompt;
                break;
            }
        }
        return $systemContent;
    }

    /**
     * Prepares the message history for Claude.
     * Claude's API is strict: it requires an alternating sequence of 'user' and 'assistant' roles.
     * It does not accept 'system' messages in the main message array.
     */
    private function prepareMessages(array $messages): array
    {
        $claudeMessages = [];
        foreach ($messages as $message) {
            // Only include 'user' and 'assistant' messages in the main conversation history.
            if (in_array($message['role'], ['user', 'assistant'])) {
                $claudeMessages[] = [
                    'role' => $message['role'],
                    'content' => $message['content']
                ];
            }
        }

        // Ensure the conversation starts with a 'user' message, as required by the API.
        if (empty($claudeMessages) || $claudeMessages[0]['role'] !== 'user') {
            // This is a safeguard, though our app logic should prevent this.
            // Prepending a dummy user message if the sequence is wrong.
            array_unshift($claudeMessages, ['role' => 'user', 'content' => '(Start of conversation)']);
        }

        return $claudeMessages;
    }
}
