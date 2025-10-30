<?php
// File: tests/Feature/Chat/ConversationTest.php

namespace Tests\Feature\Chat;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Services\Ai\AiServiceInterface;
use Mockery\MockInterface;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test that an authenticated user can start a new conversation.
     */
    public function test_an_authenticated_user_can_start_a_new_conversation(): void
    {
        // Mock the AI Service. We expect its 'ask' method to be called once.
        $this->mock(AiServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('ask')->once()->andReturn('This is a mock AI response.');
        });

        $response = $this->actingAs($this->user)->postJson('/api/chat/ask', [
            'prompt' => 'This is the first message.',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'response' => 'This is a mock AI response.',
                 ])
                 ->assertJsonStructure(['conversation_id']);

        $this->assertDatabaseHas('conversations', [
            'user_id' => $this->user->id,
            'title' => 'This is the first message.',
        ]);

        $this->assertDatabaseCount('messages', 2); // 1 from user, 1 from AI
    }

    /**
     * Test that an unauthenticated user cannot access the chat endpoint.
     */
    public function test_an_unauthenticated_user_cannot_access_chat(): void
    {
        $response = $this->postJson('/api/chat/ask', ['prompt' => 'Hello?']);
        $response->assertStatus(401); // Assert Unauthorized
    }
    
    /**
     * Test that a user can delete their own conversation.
     */
    public function test_a_user_can_delete_their_own_conversation(): void
    {
        $conversation = $this->user->conversations()->create(['title' => 'To be deleted']);

        $response = $this->actingAs($this->user)->deleteJson("/api/conversations/{$conversation->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
    }

    /**
     * Test that a user cannot delete another user's conversation.
     */
    public function test_a_user_cannot_delete_another_users_conversation(): void
    {
        $otherUser = User::factory()->create();
        $conversation = $otherUser->conversations()->create(['title' => 'Protected conversation']);

        // Authenticated as $this->user, trying to delete $otherUser's conversation
        $response = $this->actingAs($this->user)->deleteJson("/api/conversations/{$conversation->id}");

        $response->assertStatus(403); // Assert Forbidden
        $this->assertDatabaseHas('conversations', ['id' => $conversation->id]);
    }

}
