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
        Schema::create('newsletters_contact_import_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_group_id');
            $table->string('model_field');
            $table->string('csv_field')->nullable();
            $table->unsignedInteger('csv_index')->nullable();
            $table->timestamps();

            $table->foreign('contact_group_id')
                ->references('id')
                ->on('newsletters_contact_groups')
                ->cascadeOnDelete();

            $table->unique(['contact_group_id', 'model_field'], 'contact_group_model_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_contact_import_mappings');
    }
};

