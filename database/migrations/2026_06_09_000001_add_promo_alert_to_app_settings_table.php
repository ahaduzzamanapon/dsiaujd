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
        Schema::table('app_settings', function (Blueprint $table) {
            $table->boolean('promo_show_alert')->default(false)->after('welcome_message');
            $table->string('promo_title')->default('Join Telegram')->after('promo_show_alert');
            $table->text('promo_message')->nullable()->after('promo_title');
            $table->string('promo_link')->nullable()->after('promo_message');
            $table->string('promo_close_text')->default('Close')->after('promo_link');
            $table->string('promo_go_text')->default('Join Now')->after('promo_close_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn([
                'promo_show_alert',
                'promo_title',
                'promo_message',
                'promo_link',
                'promo_close_text',
                'promo_go_text',
            ]);
        });
    }
};
