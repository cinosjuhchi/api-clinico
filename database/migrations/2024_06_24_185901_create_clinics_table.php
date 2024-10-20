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
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name', 125)->unique(); 
            $table->string('company');
            $table->bigInteger('ssm_number');
            $table->bigInteger('registration_number');
            $table->bigInteger('referral_number');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('address')->nullable();            
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
