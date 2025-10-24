<?php
// File: app/Providers/AiServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Ai\AiServiceInterface;
use App\Services\Ai\ChatGptService;
use App\Services\Ai\GeminiService;
// Don't forget to import other services like ClaudeAiService
use App\Models\Setting; // We will use a Setting model later
use Illuminate\Support\Facades\Schema;

class AiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // We use a singleton because we don't need to create the service object more than once per request.
        $this->app->singleton(AiServiceInterface::class, function ($app) {
            
            // Here you would fetch the setting from the database.
            // For now, we fall back to the config file if the database/table doesn't exist yet.
            $serviceName = config('ai.active_service');
            if (Schema::hasTable('settings')) {
                $setting = Setting::where('key', 'active_ai_service')->first();
                if ($setting) {
                    $serviceName = $setting->value;
                }
            }

            switch ($serviceName) {
                case 'gemini':
                    $apiKey = config('ai.services.gemini.key');
                    return new GeminiService($apiKey);
                case 'chatgpt':
                default: // Fallback to chatgpt if the setting is invalid
                    $apiKey = config('ai.services.chatgpt.key');
                    return new ChatGptService($apiKey);
                // case 'claude':
                //     return new ClaudeAiService(config('ai.services.claude.key'));
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