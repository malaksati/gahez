<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_options', function (Blueprint $table) {
            if (! Schema::hasColumn('variant_options', 'variant_id')) {
                $table->foreignId('variant_id')->after('id')->constrained('variants')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('variant_options', function (Blueprint $table) {
            if (Schema::hasColumn('variant_options', 'variant_id')) {
                $table->dropConstrainedForeignId('variant_id');
            }
        });
    }
};
