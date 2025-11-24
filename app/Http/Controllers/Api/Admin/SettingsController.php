<?php
// File: app/Http/Controllers/Api/Admin/SettingsController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display the current AI settings.
     * We use the SettingsService to ensure we get the correct data, including defaults.
     */
    public function show(SettingsService $settingsService)
    {
        // The service already handles defaults, so we just get the DTO and convert to array.
        $settings = $settingsService->getAiSettings();

        return response()->json((array) $settings);
    }

    /**
     * Update the AI settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', Rule::in(['chatgpt', 'gemini', 'claude'])],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2.0'],
            'max_tokens' => ['required', 'integer', 'min:1'],
            'system_prompt' => ['nullable', 'string', 'max:4000'],
        ]);

        // Use updateOrCreate to find the 'ai_settings' key or create it if it doesn't exist.
        Setting::updateOrCreate(
            ['key' => 'ai_settings'],
            ['value' => json_encode($validated)]
        );

        // Since SettingsService uses caching, we must clear the cache
        // to ensure the next request reads the new settings from the database.
        Cache::forget('ai_settings');

        return response()->json([
            'message' => 'AI settings updated successfully.',
            'settings' => $validated,
        ]);
    }
}
