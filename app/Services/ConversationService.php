<?php
// File: app/Services/ConversationService.php (Final Version)

namespace App\Services;

use App\Models\Conversation;
use App\Services\Ai\AiServiceFactory;
use Illuminate\Support\Str;
use App\Data\AiSettings;

/**
 * This service class encapsulates the core business logic for handling AI conversations.
 * It manages message history, summarization, title generation, and dynamic AI provider selection.
 */
class ConversationService
{
    /**
     * The number of user/assistant messages before a summarization is triggered.
     */
    private const SUMMARIZATION_THRESHOLD = 20;

    /**
     * The factory used to create AI service instances on the fly.
     * @var \App\Services\Ai\AiServiceFactory
     */
    protected AiServiceFactory $aiServiceFactory;

    /**
     * The constructor injects the AiServiceFactory.
     */
    public function __construct(AiServiceFactory $aiServiceFactory)
    {
        $this->aiServiceFactory = $aiServiceFactory;
    }

    /**
     * The main method to process a user's prompt within a conversation.
     * It orchestrates saving messages, setting titles, summarizing, and getting an AI response.
     *
     * @param Conversation $conversation The current conversation model.
     * @param string $prompt The user's new prompt.
     * @param AiSettings $settings The global AI settings.
     * @param string|null $providerOverride An optional provider name from the user's request.
     * @return string The generated AI response.
     */
    public function processMessage(Conversation $conversation, string $prompt, AiSettings $settings, ?string $providerOverride = null): string
    {
        // Set the conversation title based on the first prompt.
        if (is_null($conversation->title) && $conversation->messages()->count() === 0) {
            $conversation->title = Str::limit($prompt, 250);
            $conversation->save();
        }

        // Save the user's new message to the database.
        $conversation->messages()->create(['role' => 'user', 'content' => $prompt]);

        // Check if the conversation has reached the length threshold for summarization.
        $messageCount = $conversation->messages()->whereIn('role', ['user', 'assistant'])->count();
        if ($messageCount > self::SUMMARIZATION_THRESHOLD && ($messageCount % self::SUMMARIZATION_THRESHOLD) === 1) {
            $this->summarize($conversation, $settings, $providerOverride);
        }

        // Build the context (history) to be sent to the AI.
        $context = $this->buildContext($conversation);
        
        // Determine which AI provider to use for this specific request.
        $providerToUse = $providerOverride ?? $settings->provider;
        $aiService = $this->aiServiceFactory->make($providerToUse);
        
        // Get the AI's response by calling the selected service.
        $aiResponseContent = $aiService->ask($context, $settings);

        // Save the AI's response to the database.
        $conversation->messages()->create(['role' => 'assistant', 'content' => $aiResponseContent]);

        return $aiResponseContent;
    }

    /**
     * Generates and saves a summary of the recent conversation history.
     */
    private function summarize(Conversation $conversation, AiSettings $settings, ?string $providerOverride = null): void
    {
        $lastSummary = $conversation->messages()->where('role', 'system')->latest()->first();

        $messagesToSummarize = $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->latest()
            ->take(self::SUMMARIZATION_THRESHOLD)
            ->get()
            ->reverse();

        $summaryPrompt = "Summarize the key points of the following conversation concisely.\n\n";

        if ($lastSummary) {
            $summaryPrompt .= "Here is the summary of the conversation so far:\n---PREVIOUS SUMMARY---\n{$lastSummary->content}\n---END PREVIOUS SUMMARY---\n\n";
        }

        $summaryPrompt .= "Now summarize the most recent part of the conversation:\n---RECENT MESSAGES---\n";

        foreach ($messagesToSummarize as $message) {
            $summaryPrompt .= "{$message->role}: {$message->content}\n";
        }
        $summaryPrompt .= "---END RECENT MESSAGES---\n\nYour summary:";

        $providerToUse = $providerOverride ?? $settings->provider;
        $aiService = $this->aiServiceFactory->make($providerToUse);

        // Make a separate API call just for summarization.
        $newSummaryContent = $aiService->ask(
            [['role' => 'user', 'content' => $summaryPrompt]],
            $settings
        );

        // Save the new summary as a 'system' message.
        $conversation->messages()->create([
            'role' => 'system',
            'content' => $newSummaryContent
        ]);
    }

    /**
     * Builds the context to be sent to the AI, using the latest summary if available.
     */
    private function buildContext(Conversation $conversation): array
    {
        $latestSummary = $conversation->messages()->where('role', 'system')->latest()->first();
        $query = $conversation->messages();

        if ($latestSummary) {
            $query->where('id', '>', $latestSummary->id);
        }

        $recentMessages = $query->whereIn('role', ['user', 'assistant'])->get(['role', 'content']);
        
        $context = [];
        if ($latestSummary) {
            // The summary acts as the initial 'system' prompt/context.
            $context[] = ['role' => 'system', 'content' => "This is a summary of the conversation so far: " . $latestSummary->content];
        }

        // Append the recent messages to the context.
        return array_merge($context, $recentMessages->toArray());
    }
}