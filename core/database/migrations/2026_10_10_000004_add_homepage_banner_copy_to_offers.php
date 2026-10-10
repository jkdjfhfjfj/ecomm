<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('banner_title', 120)->nullable()->after('banner');
            $table->text('banner_message')->nullable()->after('banner_title');
            $table->string('banner_button_text', 40)->nullable()->after('banner_message');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['banner_title', 'banner_message', 'banner_button_text']);
        });
    }
};
