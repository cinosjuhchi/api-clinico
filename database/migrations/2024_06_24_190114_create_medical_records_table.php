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
            $table->text('patient_condition');
            $table->string('diagnosis', 255);
            $table->string('procedure');
            $table->string('injection');            
            $table->text('consultation_note');
            $table->string('medicine');
            $table->string('frequency');
            $table->text('physical_examination');
            $table->string('blood_pressure');
            $table->integer('sp02');
            $table->integer('temperature');
            $table->softDeletes();

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