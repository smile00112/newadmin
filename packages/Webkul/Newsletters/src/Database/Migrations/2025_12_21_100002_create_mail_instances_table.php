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
        Schema::create('newsletters_mail_instances', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('host');
            $table->integer('port')->default(587);
            $table->string('username');
            $table->string('password');
            $table->enum('encryption', ['tls', 'ssl', 'none'])->default('tls');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('mailing_list_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->foreign('mailing_list_id')
                ->references('id')
                ->on('newsletters_mailing_lists')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_mail_instances');
    }
};



