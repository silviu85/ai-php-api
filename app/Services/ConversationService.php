<?php
// File: app/Services/ConversationService.php

namespace App\Services;

use App\Models\Conversation;
use App\Services\Ai\AiServiceInterface;
use Illuminate\Support\Str;


class ConversationService
{
    // The number of messages before triggering a summarization.
    private const SUMMARIZATION_THRESHOLD = 20;

    protected AiServiceInterface $aiService;

    public function __construct(AiServiceInterface $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Main method to process a user's prompt within a conversation.
     * It handles the logic for summarization when the threshold is reached.
     */
    public function processMessage(Conversation $conversation, string $prompt): string
    {

          if (is_null($conversation->title) && $conversation->messages()->count() === 0) {
            $conversation->title = Str::limit($prompt, 250);
            $conversation->save();
        }

        // 1. Save the user's new message.
        $conversation->messages()->create(['role' => 'user', 'content' => $prompt]);

        // 2. Check if it's time to summarize.
        // We count only user/assistant messages for the threshold.
        $messageCount = $conversation->messages()->whereIn('role', ['user', 'assistant'])->count();

        // The trigger happens on the message AFTER the threshold (e.g., the 21st, 41st).
        if ($messageCount > self::SUMMARIZATION_THRESHOLD && ($messageCount % self::SUMMARIZATION_THRESHOLD) === 1) {
            $this->summarize($conversation);
        }

        // 3. Prepare the context for the actual response.
        $context = $this->buildContext($conversation);
        
        // 4. Get the AI's response based on the (potentially summarized) context.
        $aiResponseContent = $this->aiService->ask($context);

        // 5. Save the AI's response.
        $conversation->messages()->create(['role' => 'assistant', 'content' => $aiResponseContent]);

        return $aiResponseContent;
    }

    /**
     * Generates and saves a summary of the recent conversation history.
     */
    private function summarize(Conversation $conversation): void
    {
        // Get the last summary, if it exists, to include it for cumulative context.
        $lastSummary = $conversation->messages()
            ->where('role', 'system')
            ->latest()
            ->first();

        // Get the last 20 user/assistant messages to be summarized.
        $messagesToSummarize = $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->latest()
            ->take(self::SUMMARIZATION_THRESHOLD)
            ->get()
            ->reverse(); // Reverse to maintain chronological order for the AI.

        $summaryPrompt = "Summarize the key points of the following conversation concisely.\n\n";

        if ($lastSummary) {
            $summaryPrompt .= "Here is the summary of the conversation so far:\n---PREVIOUS SUMMARY---\n{$lastSummary->content}\n---END PREVIOUS SUMMARY---\n\n";
        }
        
        $summaryPrompt .= "Now summarize the most recent part of the conversation:\n---RECENT MESSAGES---\n";

        foreach ($messagesToSummarize as $message) {
            $summaryPrompt .= "{$message->role}: {$message->content}\n";
        }
        $summaryPrompt .= "---END RECENT MESSAGES---\n\nYour summary:";

        // We make a separate API call just for summarization.
        $newSummaryContent = $this->aiService->ask([
            ['role' => 'user', 'content' => $summaryPrompt]
        ]);

        // Save the new summary as a 'system' message.
        $conversation->messages()->create([
            'role' => 'system',
            'content' => $newSummaryContent
        ]);
    }

    /**
     * Builds the context to be sent to the AI for generating a response.
     */
    private function buildContext(Conversation $conversation): array
    {
        // Get the latest summary, if any.
        $latestSummary = $conversation->messages()
            ->where('role', 'system')
            ->latest()
            ->first();

        $query = $conversation->messages();

        if ($latestSummary) {
            // If a summary exists, get all messages AFTER it.
            $query->where('id', '>', $latestSummary->id);
        }

        // We only care about user and assistant messages for the final context.
        $recentMessages = $query->whereIn('role', ['user', 'assistant'])->get(['role', 'content']);
        
        $context = [];
        if ($latestSummary) {
            // The summary acts as the initial 'system' prompt.
            $context[] = ['role' => 'system', 'content' => "This is a summary of the conversation so far: " . $latestSummary->content];
        }

        // Append the recent messages to the context.
        return array_merge($context, $recentMessages->toArray());
    }
}