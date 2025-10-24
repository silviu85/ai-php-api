<?php
// File: app/Services/Ai/GeminiService.php (Versiunea Finală și Completă)

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;

class GeminiService implements AiServiceInterface
{
    protected string $apiKey;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent';


    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }
    
    public function ask(array $messages): string
    {
        $geminiContents = [];
        $systemSummary = null;

        // First, extract the system summary, if it exists
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemSummary = $message['content'];
                break;
            }
        }

        $isFirstUserMessage = true;
        foreach ($messages as $message) {
            if (in_array($message['role'], ['user', 'assistant'])) {
                $role = ($message['role'] === 'assistant') ? 'model' : 'user';
                $content = $message['content'];

                // If a summary exists and this is the first user message, prepend the summary
                if ($systemSummary && $role === 'user' && $isFirstUserMessage) {
                    $content = "Here is a summary of our conversation so far: '{$systemSummary}'. Now, please respond to my next prompt: '{$content}'";
                    $isFirstUserMessage = false;
                }

                 $geminiContents[] = [
                    'role' => $role,
                    'parts' => [['text' => $content]]
                ];
            }
        }

        try {

            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => $geminiContents
            ]);

            $response->throw();
            
            return Arr::get($response->json(), 'candidates.0.content.parts.0.text') ?? 'Sorry, I could not get a response from Gemini.';

        } catch (RequestException $e) {
            report($e);
            return 'Error communicating with the Gemini API: ' . $e->getMessage();
        }
    }
}