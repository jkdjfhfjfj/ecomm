<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('external_source', 64)->nullable();
            $table->string('external_id', 100)->nullable();
            $table->unique(['external_source', 'external_id'], 'categories_external_identity_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('external_source', 64)->nullable();
            $table->string('external_id', 100)->nullable();
            $table->text('external_image_url')->nullable();
            $table->longText('external_details')->nullable();
            $table->unique(['external_source', 'external_id'], 'products_external_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_external_identity_unique');
            $table->dropColumn(['external_source', 'external_id', 'external_image_url', 'external_details']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_external_identity_unique');
            $table->dropColumn(['external_source', 'external_id']);
        });
    }
};
