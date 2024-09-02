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
        Schema::create('demographic_information', function (Blueprint $table) {
            $table->id();
            $table->string('mrn', 20)->nullable();
            $table->date('date_birth')->nullable();
            $table->enum('gender',['male', 'female'])->nullable();
            $table->char('nric', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('country')->nullable();
            $table->integer('postal_code')->nullable();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demographic_information');
    }
};
