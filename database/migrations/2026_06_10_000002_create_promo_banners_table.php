<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop the temporary promo banner fields from app_settings
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn([
                'promo_banner_enabled',
                'promo_banner_title',
                'promo_banner_subtitle',
                'promo_banner_logo',
                'promo_banner_countdown',
                'promo_banner_btn_text',
                'promo_banner_btn_link',
                'promo_banner_stream1_id',
                'promo_banner_stream2_id',
                'promo_banner_stream3_id',
            ]);
        });

        // 2. Create the promo_banners table
        Schema::create('promo_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle');
            $table->string('logo')->nullable();
            $table->dateTime('countdown')->nullable();
            $table->string('btn_text')->nullable();
            $table->string('btn_link')->nullable();
            
            // Channel quick-play links
            $table->unsignedBigInteger('stream1_id')->nullable();
            $table->unsignedBigInteger('stream2_id')->nullable();
            $table->unsignedBigInteger('stream3_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_banners');

        Schema::table('app_settings', function (Blueprint $table) {
            $table->boolean('promo_banner_enabled')->default(false)->after('promo_go_text');
            $table->string('promo_banner_title')->default('FIFA WORLD CUP 2026')->after('promo_banner_enabled');
            $table->string('promo_banner_subtitle')->default('🏆 ⚽ ফিফা বিশ্বকাপ ২০২৬ সরাসরি দেখুন')->after('promo_banner_title');
            $table->string('promo_banner_logo')->nullable()->after('promo_banner_subtitle');
            $table->dateTime('promo_banner_countdown')->nullable()->after('promo_banner_logo');
            $table->string('promo_banner_btn_text')->default('Watch Live Now')->after('promo_banner_countdown');
            $table->string('promo_banner_btn_link')->nullable()->after('promo_banner_btn_text');
            $table->unsignedBigInteger('promo_banner_stream1_id')->nullable()->after('promo_banner_btn_link');
            $table->unsignedBigInteger('promo_banner_stream2_id')->nullable()->after('promo_banner_stream1_id');
            $table->unsignedBigInteger('promo_banner_stream3_id')->nullable()->after('promo_banner_stream2_id');
        });
    }
};
