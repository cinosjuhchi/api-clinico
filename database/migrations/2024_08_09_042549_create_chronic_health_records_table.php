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
        Schema::create('chronic_health_records', function (Blueprint $table) {
            $table->id();
            $table->string('chronic_medical')->nullable();
            $table->string('father_chronic_medical')->nullable();
            $table->string('mother_chronic_medical')->nullable();
            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chronic_health_records');
    }
};
