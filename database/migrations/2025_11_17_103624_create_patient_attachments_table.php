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
        Schema::create('patient_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');

            $table->enum('type', ['prescription', 'lab', 'radiology', 'scan', 'other']);
            $table->string('file_path');
            $table->string('description')->nullable();
            $table->enum('source', ['patient', 'doctor', 'external'])->default('patient');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_attachments');
    }
};
