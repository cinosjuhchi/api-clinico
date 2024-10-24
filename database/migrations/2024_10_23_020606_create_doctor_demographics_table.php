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
        Schema::create('doctor_demographics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('birth_date');
            $table->string('place_of_birth');
            $table->enum('gender',['male', 'female']);
            $table->enum('marital_status',['Married', 'Single']);
            $table->char('nric', 100);
            $table->string('address', 255);
            $table->string('country');
            $table->integer('postal_code');            
            $table->string('email');
            $table->string('phone_number');
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_demographics');
    }
};
