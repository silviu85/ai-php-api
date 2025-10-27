<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Services\ConversationService;

class ChatController extends Controller
{
    // Inject the service via the constructor for better testability and performance.
    protected ConversationService $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

   public function conversation(Request $request, ConversationService $conversationService)
{
    $validated = $request->validate([
        'prompt' => 'required|string|min:1|max:4000',
        'conversation_id' => 'nullable|integer|exists:conversations,id',
    ]);

    $user = $request->user();

    
    $conversationId = $validated['conversation_id'] ?? null;
    // -----------------------------

    
    if ($conversationId) {
        // IMPORTANT: Security check to ensure the user owns this conversation
        $conversation = $user->conversations()->findOrFail($conversationId);
    } else {
        
        $conversation = $user->conversations()->create([]);
    }

    // Delegate all the complex logic to the service.
    $aiResponseContent = $this->conversationService->processMessage(
        $conversation,
        $validated['prompt']
    );

    return response()->json([
        'response' => $aiResponseContent,
        'conversation_id' => $conversation->id, // ALWAYS return the ID
    ]);
}
}