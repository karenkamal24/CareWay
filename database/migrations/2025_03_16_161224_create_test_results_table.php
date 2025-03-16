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
        Schema::create('test_results', function (Blueprint $table) {
           
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('patient_name');
            $table->string('patient_email')->nullable();
            
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->string('doctor_name')->nullable(); 
            $table->string('doctor_email')->nullable();
            
            $table->text('note')->nullable();
            $table->string('age')->nullable();
            $table->date('test_date');
            $table->date('result_date');
            $table->json('tests'); 
            $table->decimal('total_cost', 10, 2)->nullable(); 
            $table->decimal('amount_paid', 10, 2)->default(0); 
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');     
            $table->enum('test_status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
