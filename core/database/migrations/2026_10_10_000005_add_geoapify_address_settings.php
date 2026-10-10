<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('geoapify_address_enabled')->default(false);
            $table->string('geoapify_country_codes', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['geoapify_address_enabled', 'geoapify_country_codes']);
        });
    }
};
