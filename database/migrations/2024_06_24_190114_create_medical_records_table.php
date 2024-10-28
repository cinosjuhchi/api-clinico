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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->text('patient_condition')->nullable();                          
            $table->text('consultation_note')->nullable();                        
            $table->text('physical_examination')->nullable();
            $table->string('blood_pressure');            
            $table->text('plan')->nullable();
            $table->integer('sp02');
            $table->integer('temperature');
            $table->integer('pulse_rate');            
            $table->integer('pain_score')->default(0);

            $table->softDeletes();

            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_service_id')->nullable()->constrained()->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};