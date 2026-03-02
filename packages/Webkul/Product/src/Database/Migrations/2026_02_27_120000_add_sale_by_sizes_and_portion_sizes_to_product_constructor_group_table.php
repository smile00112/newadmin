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
        Schema::table('product_constructor_group', function (Blueprint $table) {
            $table->boolean('sale_by_sizes')->default(false)->after('ingredients_incompatibilities_id');
            $table->json('portion_sizes')->nullable()->after('sale_by_sizes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_constructor_group', function (Blueprint $table) {
            $table->dropColumn(['sale_by_sizes', 'portion_sizes']);
        });
    }
};
