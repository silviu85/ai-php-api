<?php

return [
    'active_service' => env('ACTIVE_AI_SERVICE', 'chatgpt'),

    'services' => [
        'chatgpt' => [
            'key' => env('OPENAI_API_KEY'),
        ],
        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
        ],
        'claude' => [
            'key' => env('CLAUDE_API_KEY'),
        ],
    ],
];