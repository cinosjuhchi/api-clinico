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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('image_profile')->nullable();
            $table->string('image_signature')->nullable();
            $table->string('branch');
            $table->string('position');            
            $table->integer('mmc');
            $table->string('apc');
            $table->string('staff_id');
            $table->string('tenure');
            $table->decimal('basic_salary', 8, 2);
            $table->decimal('elaun', 8, 2);            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
