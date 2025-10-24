<?php

namespace App\Services\Ai;

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
    $response = $this->client->chat()->create([
        'model' => 'gpt-4o',
        'messages' => $messages, // Pass the whole history
    ]);

    return $response->choices[0]->message->content ?? 'Sorry, I could not get a response.';
}
}