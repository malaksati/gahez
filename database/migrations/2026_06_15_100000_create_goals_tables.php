<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->enum('period_type', ['daily', 'weekly', 'monthly']);
            $table->decimal('min_order_total', 10, 2);
            $table->decimal('reward_amount', 10, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
            $table->index('period_type');
        });

        Schema::create('goal_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('goals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('order_total', 10, 2);
            $table->decimal('reward_amount', 10, 2);
            $table->timestamp('awarded_at');
            $table->timestamps();

            $table->unique(['goal_id', 'user_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_achievements');
        Schema::dropIfExists('goals');
    }
};
