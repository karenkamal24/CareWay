<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patient_medications', function (Blueprint $table) {
            $table->foreignId('visit_id')->nullable()->constrained('visits')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('patient_medications', function (Blueprint $table) {
            $table->dropColumn('visit_id');
        });
    }

};
