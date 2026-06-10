<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->enum('type', ['fixed', 'percentage']);
            $table->decimal('value', 10, 2);
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('offerable_id');
            $table->string('offerable_type');
            $table->timestamps();

            $table->index(['offerable_id', 'offerable_type']); //polymorphic relation index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
