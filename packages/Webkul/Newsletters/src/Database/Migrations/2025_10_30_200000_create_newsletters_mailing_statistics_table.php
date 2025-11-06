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
        Schema::create('newsletters_mailing_statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mailing_list_id');
            $table->unsignedBigInteger('customer_number_id');
            $table->string('event_type'); // sent, opened, clicked, delivered, bounced etc
            $table->timestamp('event_time')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('mailing_list_id', 'mli_id_nwsml')->references('id')->on('newsletters_mailing_lists')->onDelete('cascade');
            $table->foreign('customer_number_id', 'cni_id_nwscn')->references('id')->on('newsletters_customer_numbers')->onDelete('cascade');
            $table->index(['mailing_list_id','customer_number_id','event_type'], ',mlid_cnid_ety');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_mailing_statistics');
    }
};
