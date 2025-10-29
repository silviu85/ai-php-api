<?php
// File: app/Http/Controllers/Api/ChatController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ConversationService;
use App\Services\SettingsService;
use App\Services\Ai\AiServiceInterface;


class ChatController extends Controller
{
    /**
     * Services are injected via the constructor for better performance and testability.
     * They are available throughout the class via $this->serviceName.
     */
    public function __construct(
        protected ConversationService $conversationService,
        protected AiServiceInterface $aiService,
        protected SettingsService $settingsService
    ) {}

    /**
     * Handle the main conversation request.
     */
    public function conversation(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|min:1|max:4000',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
        ]);

        $user = $request->user();
        
        $conversationId = $validated['conversation_id'] ?? null;
        
        if ($conversationId) {
            // IMPORTANT: Security check to ensure the user owns this conversation
            $conversation = $user->conversations()->findOrFail($conversationId);
        } else {
            // Create a new, untitled conversation
            $conversation = $user->conversations()->create([]);
        }

        // Get the current AI settings using the service injected in the constructor
        $settings = $this->settingsService->getAiSettings();

        // Delegate all the complex logic to the conversation service
        // using the service instance from the constructor.
        $aiResponseContent = $this->conversationService->processMessage(
            $conversation,
            $validated['prompt'],
            $settings
        );

        return response()->json([
            'response' => $aiResponseContent,
            'conversation_id' => $conversation->id,
        ]);
    }
}
