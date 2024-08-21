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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('category_id');
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->softDeletes();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
