<?php
// File: database/seeders/AiSettingsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting; // Import the Setting model

class AiSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the default settings for Gemini
        $geminiSettings = [
            'provider' => 'gemini',
            'temperature' => 0.7,
            'max_tokens' => 2048,
            'system_prompt' => 'You are a friendly and helpful AI assistant powered by Google Gemini.',
        ];

        // Use updateOrCreate to insert the setting if it doesn't exist,
        // or update it if it already does. This prevents duplicate entries.
        Setting::updateOrCreate(
            ['key' => 'ai_settings'],
            ['value' => json_encode($geminiSettings)]
        );
    }
}
