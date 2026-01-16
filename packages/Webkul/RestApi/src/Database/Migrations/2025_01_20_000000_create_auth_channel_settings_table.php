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
        Schema::create('auth_channel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index(); // 'sms', 'telegram', 'whatsapp'
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('channel_code')->nullable()->index();
            $table->timestamps();

            $table->unique(['channel', 'key', 'channel_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_channel_settings');
    }
};
