<?php
// File: app/Http/Controllers/Api/ChatController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ConversationService;
use App\Services\SettingsService;
use Illuminate\Validation\Rule;


class ChatController extends Controller
{
    /**
     * Constructor
     */
    public function __construct(protected ConversationService $conversationService,protected SettingsService $settingsService) 
    {

    }

    /**
     * Handle the main conversation request.
     */
    public function conversation(Request $request)
    {

     $validated = $request->validate([
        'prompt' => 'required|string|min:1|max:4000',
        'conversation_id' => 'nullable|integer|exists:conversations,id',
        'provider' => ['nullable', 'string', Rule::in(['chatgpt', 'gemini', 'claude'])],
    ]);
        $user = $request->user();
        $conversationId = $validated['conversation_id'] ?? null;

        if ($conversationId) {
            $conversation = $user->conversations()->findOrFail($conversationId);
        } else {
            $conversation = $user->conversations()->create([]);
        }

        $settings = $this->settingsService->getAiSettings();

        $aiResponseContent = $this->conversationService->processMessage(
            $conversation,
            $validated['prompt'],
            $settings,
            $validated['provider'] ?? null
        );

        return response()->json([
            'response' => $aiResponseContent,
            'conversation_id' => $conversation->id,
        ]);
    }
}
