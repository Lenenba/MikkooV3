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
        Schema::table('reservation_details', function (Blueprint $table) {
            if (! Schema::hasColumn('reservation_details', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('status');
            }
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(
                "ALTER TABLE reservation_details MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'canceled') DEFAULT 'pending'"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(
                "ALTER TABLE reservation_details MODIFY COLUMN status ENUM('pending', 'confirmed', 'canceled') DEFAULT 'pending'"
            );
        }

        Schema::table('reservation_details', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_details', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};
