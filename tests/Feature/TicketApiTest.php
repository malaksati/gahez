<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_complaint_ticket(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tickets', [
            'type' => Ticket::TYPE_COMPLAINT,
            'subject' => 'Wrong item',
            'description' => 'I received the wrong product.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', Ticket::TYPE_COMPLAINT)
            ->assertJsonPath('data.subject', 'Wrong item');

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'type' => Ticket::TYPE_COMPLAINT,
            'subject' => 'Wrong item',
        ]);
    }

    public function test_customer_can_create_recommendation_ticket(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tickets', [
            'type' => Ticket::TYPE_RECOMMENDATION,
            'subject' => 'Add more brands',
            'description' => 'Please add organic brands.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', Ticket::TYPE_RECOMMENDATION);
    }

    public function test_ticket_type_is_required(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tickets', [
            'subject' => 'Missing type',
            'description' => 'No type provided.',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }
}
