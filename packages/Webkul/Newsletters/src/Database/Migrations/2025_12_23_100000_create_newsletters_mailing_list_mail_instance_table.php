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
        Schema::create('newsletters_mailing_list_mail_instance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mailing_list_id');
            $table->unsignedBigInteger('mail_instance_id');
            $table->timestamps();

            $table->foreign('mailing_list_id', 'ml_mll')
                ->references('id')
                ->on('newsletters_mailing_lists')
                ->onDelete('cascade');

            $table->foreign('mail_instance_id', 'ml_mi')
                ->references('id')
                ->on('newsletters_mail_instances')
                ->onDelete('cascade');

            $table->unique(['mailing_list_id', 'mail_instance_id'], 'ml_ml_mi_uk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_mailing_list_mail_instance');
    }
};

