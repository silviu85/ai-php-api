<?php
// File: app/Services/Ai/AiServiceFactory.php

namespace App\Services\Ai;

use Illuminate\Contracts\Config\Repository as Config;
use InvalidArgumentException;

/**
 * Factory class responsible for creating instances of AI services.
 */
class AiServiceFactory
{
    /**
     * The config repository instance.
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Create a new factory instance.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Create an instance of the specified AI service.
     *
     * @param string $provider The name of the provider (e.g., 'chatgpt', 'gemini').
     * @return AiServiceInterface
     * @throws \InvalidArgumentException
     */
    public function make(string $provider): AiServiceInterface
    {
        switch ($provider) {
            case 'gemini':
                $apiKey = $this->config->get('ai.services.gemini.key');
                if (!$apiKey)
                {
                    throw new InvalidArgumentException("API key for Gemini is not configured.");
                }
                return new GeminiService($apiKey);

            case 'chatgpt':
                $apiKey = $this->config->get('ai.services.chatgpt.key');
                if (!$apiKey)
                {
                    throw new InvalidArgumentException("API key for OpenAI is not configured.");
                }
                return new ChatGptService($apiKey);

             case 'claude':
                 $apiKey = $this->config->get('ai.services.claude.key');
                 if (!$apiKey)
                    {
                        throw new InvalidArgumentException("API key for Claude (Anthropic) is not configured.");
                    }
                 return new ClaudeAiService($apiKey);
            default:
                throw new InvalidArgumentException("Unsupported AI provider requested: [{$provider}]");
        }
    }
}
