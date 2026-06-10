<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_transfer_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity', 32);
            $table->string('direction', 16);
            $table->string('status', 32)->default('pending');
            $table->string('file_path')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['entity', 'direction', 'status']);
        });

        Schema::create('import_row_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_transfer_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('row_data')->nullable();
            $table->json('errors');
            $table->timestamps();

            $table->index(['data_transfer_batch_id', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_row_logs');
        Schema::dropIfExists('data_transfer_batches');
    }
};
