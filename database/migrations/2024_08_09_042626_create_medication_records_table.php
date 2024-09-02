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
        Schema::create('medication_records', function (Blueprint $table) {
            $table->id();
            $table->string('medicine', 255)->nullable();
            $table->string('frequency', 255)->nullable();
            $table->string('allergy', 255)->nullable();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_records');
    }
};
