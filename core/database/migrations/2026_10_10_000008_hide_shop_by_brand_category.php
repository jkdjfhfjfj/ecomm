<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $category = DB::table('categories')
            ->where('external_source', 'parsebot_jomashop')
            ->where('external_id', '5695')
            ->whereNull('deleted_at')
            ->first();

        if (!$category) {
            return;
        }

        DB::table('categories')
            ->where('id', $category->id)
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('external_source', 'parsebot_jomashop')
            ->where('external_id', '5695')
            ->whereNotNull('deleted_at')
            ->update(['deleted_at' => null, 'updated_at' => now()]);
    }
};
