<?php

namespace Database\Factories;

use App\Models\Support;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportMessage>
 */
class SupportMessageFactory extends Factory
{
    protected $model = SupportMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_id' => Support::factory(),
            'sender_type' => 'user',
            'sender_id' => User::factory(),
            'message' => fake()->paragraph(),
            'attachments' => null,
            'read_at' => null,
        ];
    }

    public function fromAdmin(User $admin): static
    {
        return $this->state(fn () => [
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
        ]);
    }
}
