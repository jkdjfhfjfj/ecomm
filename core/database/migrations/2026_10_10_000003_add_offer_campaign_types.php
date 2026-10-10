<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('campaign_type', 24)->default('standard')->after('name');
        });

        DB::table('categories')->updateOrInsert(
            ['external_source' => 'local_promotions', 'external_id' => 'daily-offers'],
            ['name' => 'Daily Offers', 'slug' => 'daily-offers', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        $categoryId = DB::table('categories')
            ->where('external_source', 'local_promotions')
            ->where('external_id', 'daily-offers')
            ->value('id');
        if ($categoryId) {
            DB::table('category_product')->where('category_id', $categoryId)->delete();
            DB::table('categories')->where('id', $categoryId)->delete();
        }

        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('campaign_type');
        });
    }
};
