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
        Schema::create('sync_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // m3u, fancode, link-checker
            $table->string('name'); // e.g. Roar Zone Playlist
            $table->text('url')->nullable();
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_tasks');
    }
};
