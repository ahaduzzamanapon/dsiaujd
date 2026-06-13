<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_streams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('url');
            $table->string('http_referer')->nullable();
            $table->string('http_origin')->nullable();
            $table->string('category')->nullable();
            $table->string('source')->nullable(); // which sync command
            $table->string('reason')->default('failed_check'); // failed_check | manual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_streams');
    }
};
