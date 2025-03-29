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
            $table->enum('type', ['clinic', 'online'])->default('clinic');
            $table->boolean('is_booked')->default(false); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_doctors', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('is_booked'); 
        });
    }
};
