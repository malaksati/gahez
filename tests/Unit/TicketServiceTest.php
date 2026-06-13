<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Models\User;
use App\V1\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_message_resolved_ticket(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $ticket = Ticket::query()->create([
            'user_id' => $user->id,
            'type' => Ticket::TYPE_COMPLAINT,
            'subject' => 'Test subject',
            'description' => 'Test description',
            'status' => 'resolved',
        ]);

        $this->expectException(ValidationException::class);

        app(TicketService::class)->addMessage($ticket->id, [
            'message' => 'Follow up',
            'sender_type' => 'user',
            'sender_id' => $user->id,
        ]);
    }

    public function test_customer_can_message_pending_ticket(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $ticket = Ticket::query()->create([
            'user_id' => $user->id,
            'type' => Ticket::TYPE_COMPLAINT,
            'subject' => 'Test subject',
            'description' => 'Test description',
            'status' => 'pending',
        ]);

        $message = app(TicketService::class)->addMessage($ticket->id, [
            'message' => 'Follow up',
            'sender_type' => 'user',
            'sender_id' => $user->id,
        ]);

        $this->assertSame('Follow up', $message->message);
        $this->assertSame('pending', $ticket->fresh()->status);
    }

    public function test_admin_can_message_resolved_ticket_and_reopens_it(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::query()->create([
            'user_id' => $customer->id,
            'subject' => 'Test subject',
            'description' => 'Test description',
            'status' => 'resolved',
        ]);

        app(TicketService::class)->addMessage($ticket->id, [
            'message' => 'Admin reply',
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
        ]);

        $this->assertSame('pending', $ticket->fresh()->status);
    }
}
