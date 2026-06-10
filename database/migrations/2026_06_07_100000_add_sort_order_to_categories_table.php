<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('parent_id');
        });

        $rows = DB::table('categories')
            ->orderByRaw('parent_id IS NULL DESC')
            ->orderBy('parent_id')
            ->orderBy('id')
            ->get(['id', 'parent_id']);

        $orderByParent = [];

        foreach ($rows as $row) {
            $key = $row->parent_id ?? 'root';
            $orderByParent[$key] = ($orderByParent[$key] ?? 0) + 1;

            DB::table('categories')
                ->where('id', $row->id)
                ->update(['sort_order' => $orderByParent[$key]]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
