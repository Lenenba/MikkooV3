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
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('location')->nullable()->after('description');
            $table->date('start_date')->nullable()->after('location');
            $table->time('start_time')->nullable()->after('start_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('schedule_type')->default('single')->after('end_time');
            $table->string('recurrence_frequency')->nullable()->after('schedule_type');
            $table->unsignedInteger('recurrence_interval')->nullable()->after('recurrence_frequency');
            $table->json('recurrence_days')->nullable()->after('recurrence_interval');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'start_date',
                'start_time',
                'end_time',
                'schedule_type',
                'recurrence_frequency',
                'recurrence_interval',
                'recurrence_days',
                'recurrence_end_date',
            ]);
        });
    }
};
