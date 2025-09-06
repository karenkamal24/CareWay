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
        $table->tinyInteger('day_of_week')->comment('0=Sunday .. 6=Saturday');
        $table->boolean('is_recurring')->default(true);
    });
}

public function down(): void
{
    Schema::table('available_doctors', function (Blueprint $table) {
        $table->dropColumn(['day_of_week', 'is_recurring']);
    });
}
};
