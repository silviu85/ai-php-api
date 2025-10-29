<?php
// File: app/Services/SettingsService.php

namespace App\Services;

use App\Data\AiSettings;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get the current AI settings from the database, with caching.
     */
    public function getAiSettings(): AiSettings
    {
        // Cache the settings for performance.
        $settingsArray = Cache::remember('ai_settings', 60, function () {
            $setting = Setting::where('key', 'ai_settings')->first();
            
            // Return default settings if not found in the database.
            if (!$setting) {
                return [
                    'provider' => config('ai.active_service', 'chatgpt'),
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                    'system_prompt' => 'You are a helpful AI assistant.',
                ];
            }
            
            return json_decode($setting->value, true);
        });

        return AiSettings::fromArray($settingsArray);
    }
}
