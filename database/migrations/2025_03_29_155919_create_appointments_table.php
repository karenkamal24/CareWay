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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('available_doctor_id')->constrained('available_doctors')->onDelete('cascade');
            $table->enum('type', ['online', 'clinic']); 
            $table->dateTime('appointment_time'); 
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'cash'])->default('pending'); 
            $table->enum('payment_method', ['cash', 'card'])->default('cash'); 
            $table->float('amount')->nullable(); 
            $table->string('paymob_order_id')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
