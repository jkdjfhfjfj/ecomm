<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            $addExternalSource = !Schema::hasColumn('products', 'external_source');
            $addExternalId = !Schema::hasColumn('products', 'external_id');
            $addExternalImageUrl = !Schema::hasColumn('products', 'external_image_url');
            $addExternalDetails = !Schema::hasColumn('products', 'external_details');

            if ($addExternalSource || $addExternalId || $addExternalImageUrl || $addExternalDetails) {
                Schema::table('products', function (Blueprint $table) use (
                    $addExternalSource,
                    $addExternalId,
                    $addExternalImageUrl,
                    $addExternalDetails
                ) {
                    if ($addExternalSource) {
                        $table->string('external_source', 64)->nullable();
                    }
                    if ($addExternalId) {
                        $table->string('external_id', 100)->nullable();
                    }
                    if ($addExternalImageUrl) {
                        $table->text('external_image_url')->nullable();
                    }
                    if ($addExternalDetails) {
                        $table->longText('external_details')->nullable();
                    }
                });
            }
        }

        if (Schema::hasTable('categories')) {
            $addExternalSource = !Schema::hasColumn('categories', 'external_source');
            $addExternalId = !Schema::hasColumn('categories', 'external_id');
            $addExternalImageUrl = !Schema::hasColumn('categories', 'external_image_url');

            if ($addExternalSource || $addExternalId || $addExternalImageUrl) {
                Schema::table('categories', function (Blueprint $table) use (
                    $addExternalSource,
                    $addExternalId,
                    $addExternalImageUrl
                ) {
                    if ($addExternalSource) {
                        $table->string('external_source', 64)->nullable();
                    }
                    if ($addExternalId) {
                        $table->string('external_id', 100)->nullable();
                    }
                    if ($addExternalImageUrl) {
                        $table->text('external_image_url')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        // Keep repaired external catalog data intact on rollback.
    }
};
