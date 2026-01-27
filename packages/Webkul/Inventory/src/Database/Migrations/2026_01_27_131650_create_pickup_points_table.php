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
        Schema::create('pickup_points', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('latitude', 10, 5)->nullable();
            $table->decimal('longitude', 10, 5)->nullable();
            $table->text('address')->nullable();
            $table->text('working_hours')->nullable();
            $table->string('map_icon')->nullable();
            $table->integer('inventory_source_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('inventory_source_id')->references('id')->on('inventory_sources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_points');
    }
};
