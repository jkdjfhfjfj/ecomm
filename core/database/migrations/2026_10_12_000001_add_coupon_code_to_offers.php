<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('offers') && !Schema::hasColumn('offers', 'coupon_code')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->string('coupon_code', 40)->nullable();
            });
        }
    }

    public function down(): void
    {
        // Preserve configured offer coupon codes on rollback.
    }
};
