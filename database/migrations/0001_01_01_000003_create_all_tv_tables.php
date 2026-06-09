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
        // App Settings table
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_version')->default('1.0.0');
            $table->boolean('is_mandatory_update')->default(false);
            $table->text('update_message')->nullable();
            $table->string('update_url')->nullable();
            $table->text('welcome_message')->nullable();
            $table->timestamps();
        });

        // Categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Unified Streams table
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('sport_type')->default('other'); // cricket, football, other
            
            // Team configurations for live events/matches
            $table->string('team1_name')->nullable();
            $table->string('team1_logo')->nullable();
            $table->string('team2_name')->nullable();
            $table->string('team2_logo')->nullable();

            // Expiry/time configuration
            $table->boolean('is_permanent')->default(false);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('expire_time')->nullable();

            // Tab configurations (select where to show)
            $table->boolean('show_in_events')->default(false);
            $table->boolean('show_in_sports')->default(false);
            $table->boolean('show_in_tv')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Stream Servers table (multiple servers per stream)
        Schema::create('stream_servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('stream_type')->default('iframe'); // iframe or m3u8
            $table->text('url');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Pivot table Category-Stream (Many-to-Many for Live TV Categories)
        Schema::create('category_stream', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stream_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_stream');
        Schema::dropIfExists('stream_servers');
        Schema::dropIfExists('streams');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('app_settings');
    }
};
