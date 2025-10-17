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
        Schema::create('newsletters_whatsapp_instances', function (Blueprint $table) {
            $table->id();
            $table->string('link_name');
            $table->string('login');
            $table->string('password');
            $table->unsignedBigInteger('mailing_list_id')->nullable();
            $table->timestamps();

            $table->foreign('mailing_list_id')->references('id')->on('newsletters_mailing_lists')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_whatsapp_instances');
    }
};
