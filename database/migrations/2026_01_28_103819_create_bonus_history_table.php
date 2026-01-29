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
        Schema::create('bonus_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->unsigned();
            $table->integer('order_id')->unsigned()->nullable();
            $table->enum('type', ['accrual', 'deduction', 'refund', 'expiration']);
            $table->decimal('amount', 12, 4)->default(0);
            $table->decimal('base_amount', 12, 4)->default(0);
            $table->decimal('balance_after', 12, 4)->default(0);
            $table->dateTime('expires_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            
            $table->index('customer_id');
            $table->index('order_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_history');
    }
};
