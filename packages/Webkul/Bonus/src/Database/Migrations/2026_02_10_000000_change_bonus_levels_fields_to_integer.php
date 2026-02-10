<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE bonus_levels MODIFY cashback_percent INT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE bonus_levels MODIFY threshold_value INT NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bonus_levels ALTER COLUMN cashback_percent TYPE INTEGER USING (cashback_percent::integer), ALTER COLUMN cashback_percent SET DEFAULT 0');
            DB::statement('ALTER TABLE bonus_levels ALTER COLUMN threshold_value TYPE INTEGER USING (threshold_value::integer), ALTER COLUMN threshold_value SET DEFAULT 0');
        } else {
            Schema::table('bonus_levels', function (Blueprint $table) {
                $table->integer('cashback_percent')->default(0)->change();
                $table->integer('threshold_value')->default(0)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE bonus_levels MODIFY cashback_percent DECIMAL(5,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE bonus_levels MODIFY threshold_value DECIMAL(12,4) NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bonus_levels ALTER COLUMN cashback_percent TYPE DECIMAL(5,2) USING (cashback_percent::decimal), ALTER COLUMN cashback_percent SET DEFAULT 0');
            DB::statement('ALTER TABLE bonus_levels ALTER COLUMN threshold_value TYPE DECIMAL(12,4) USING (threshold_value::decimal), ALTER COLUMN threshold_value SET DEFAULT 0');
        } else {
            Schema::table('bonus_levels', function (Blueprint $table) {
                $table->decimal('cashback_percent', 5, 2)->default(0)->change();
                $table->decimal('threshold_value', 12, 4)->default(0)->change();
            });
        }
    }
};
