<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->string('reviewer_name', 100)->nullable()->after('user_id');
            $table->boolean('is_sample')->default(false)->index()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex(['is_sample']);
            $table->dropColumn(['reviewer_name', 'is_sample']);
        });
    }
};
