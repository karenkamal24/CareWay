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
        Schema::table('available_doctors', function (Blueprint $table) {
$table->unsignedInteger('capacity')
    ->default(0)
    ->after('end_time')
    ->comment('Number of available appointments');

$table->unsignedInteger('booked_count')
    ->default(0)
    ->after('capacity')
    ->comment('Number of already booked appointments');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_doctors', function (Blueprint $table) {
            Schema::table('available_doctors', function (Blueprint $table) {
        $table->dropColumn(['capacity', 'booked_count']);
    });
        });
    }
};
