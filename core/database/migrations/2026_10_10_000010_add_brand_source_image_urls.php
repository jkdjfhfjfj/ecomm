<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('brands', 'external_image_url')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->text('external_image_url')->nullable();
            });
        }

        DB::table('brands')
            ->whereNull('external_image_url')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('products')
                    ->whereColumn('products.brand_id', 'brands.id')
                    ->where('products.external_source', 'parsebot_jomashop')
                    ->whereNotNull('products.external_image_url')
                    ->where('products.external_image_url', '!=', '');
            })
            ->update([
                'external_image_url' => DB::raw("(
                    SELECT products.external_image_url
                    FROM products
                    WHERE products.brand_id = brands.id
                      AND products.external_source = 'parsebot_jomashop'
                      AND products.external_image_url LIKE 'https://%'
                    ORDER BY products.id DESC
                    LIMIT 1
                )"),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('brands', 'external_image_url')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn('external_image_url');
            });
        }
    }
};
