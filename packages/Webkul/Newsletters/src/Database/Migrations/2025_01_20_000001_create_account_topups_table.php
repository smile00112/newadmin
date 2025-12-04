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
        Schema::create('account_topups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('company_accounts')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->index('account_id');
            $table->index('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_topups');
    }
};

