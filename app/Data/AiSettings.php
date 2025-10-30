<?php
// File: app/Data/AiSettings.php

namespace App\Data;

/**
 * A Data Transfer Object to hold standardized AI settings.
 */
class AiSettings
{
    public function __construct(
        public readonly string $provider,
        public readonly float $temperature,
        public readonly int $maxTokens,
        public readonly ?string $systemPrompt
    ) {}

    /**
     * Create an instance from an array of data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            provider: $data['provider'] ?? 'chatgpt',
            temperature: (float) ($data['temperature'] ?? 0.7),
            maxTokens: (int) ($data['max_tokens'] ?? 2048),
            systemPrompt: $data['system_prompt'] ?? null
        );
    }
}
