<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('offers')) {
            return;
        }

        $addCampaignType = !Schema::hasColumn('offers', 'campaign_type');
        $addBannerTitle = !Schema::hasColumn('offers', 'banner_title');
        $addBannerMessage = !Schema::hasColumn('offers', 'banner_message');
        $addBannerButtonText = !Schema::hasColumn('offers', 'banner_button_text');
        $addShowBanner = !Schema::hasColumn('offers', 'show_banner');
        $addShowCountdown = !Schema::hasColumn('offers', 'show_countdown');

        if (!($addCampaignType || $addBannerTitle || $addBannerMessage || $addBannerButtonText || $addShowBanner || $addShowCountdown)) {
            return;
        }

        Schema::table('offers', function (Blueprint $table) use (
            $addCampaignType,
            $addBannerTitle,
            $addBannerMessage,
            $addBannerButtonText,
            $addShowBanner,
            $addShowCountdown
        ) {
            if ($addCampaignType) {
                $table->string('campaign_type', 24)->default('standard');
            }
            if ($addBannerTitle) {
                $table->string('banner_title', 120)->nullable();
            }
            if ($addBannerMessage) {
                $table->text('banner_message')->nullable();
            }
            if ($addBannerButtonText) {
                $table->string('banner_button_text', 40)->nullable();
            }
            if ($addShowBanner) {
                $table->unsignedTinyInteger('show_banner')->default(1);
            }
            if ($addShowCountdown) {
                $table->unsignedTinyInteger('show_countdown')->default(1);
            }
        });
    }

    public function down(): void
    {
        // Keep repaired offer data/schema intact on rollback.
    }
};
