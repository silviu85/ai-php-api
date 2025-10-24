<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    //
    // In Admin/SettingsController.php
public function update(Request $request)
{
    // ... validation to ensure value is 'chatgpt', 'gemini', etc.

    Setting::updateOrCreate(
        ['key' => 'active_ai_service'],
        ['value' => $request->input('service_name')]
    );
    
    return response()->json(['message' => 'AI service updated successfully.']);
}
}
