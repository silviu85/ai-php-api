<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * Display a listing of the user's conversations.
     */
    public function index(Request $request)
    {
        $conversations = $request->user()
            ->conversations()
            ->latest()
            ->select('id', 'title', 'created_at')
            ->get();
            
        return response()->json($conversations);
    }

    /**
     * Display the specified conversation with its messages.
     */
    public function show(Request $request, \App\Models\Conversation $conversation)
    {
        // Security check: ensure the user owns this conversation
        if ($request->user()->id !== $conversation->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($conversation->load('messages'));
    }
      /**
     * Remove the specified conversation from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Conversation  $conversation
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Conversation $conversation)
    {
        // --- IMPORTANT SECURITY CHECK ---
        // Ensure the authenticated user is the owner of this conversation.
        // This prevents a user from deleting someone else's conversation.
        if ($request->user()->id !== $conversation->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Delete the conversation.
        // The database is configured with cascading deletes,
        // so all associated messages will be deleted automatically.
        $conversation->delete();

        // Return a success response.
        // 200 OK with a message is often more helpful for frontends than a 204 No Content.
        return response()->json(['message' => 'Conversation deleted successfully.']);
    }
}