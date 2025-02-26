<?php

use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gestational_ages', function (Blueprint $table) {
            $table->id();
            $table->integer('gravida');
            $table->integer('para');
            $table->integer('plus');
            $table->date('menstruation_date');
            $table->foreignIdFor(MedicalRecord::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gestational_ages');
    }
};
