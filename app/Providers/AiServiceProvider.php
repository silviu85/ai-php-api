<?php
// File: app/Providers/AiServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Ai\AiServiceInterface;
use App\Services\SettingsService;
use App\Services\Ai\ChatGptService;
use App\Services\Ai\GeminiService;
// Don't forget to import other services like ClaudeAiService
use App\Models\Setting; // We will use a Setting model later

class AiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // First, register our SettingsService as a singleton.
        $this->app->singleton(SettingsService::class, fn() => new SettingsService());

        $this->app->singleton(AiServiceInterface::class, function ($app) {
            // Get the current settings from our new service.
            $settings = $app->make(SettingsService::class)->getAiSettings();
            
            switch ($settings->provider) {
                case 'gemini':
                    $apiKey = config('ai.services.gemini.key');
                    return new GeminiService($apiKey);
                // case 'claude':
                //     return new ClaudeAiService(...);
                case 'chatgpt':
                default:
                    $apiKey = config('ai.services.chatgpt.key');
                    return new ChatGptService($apiKey);
            }
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}