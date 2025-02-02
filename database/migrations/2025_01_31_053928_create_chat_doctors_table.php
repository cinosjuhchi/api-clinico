<?php

use App\Models\OnlineConsultation;
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
        Schema::create('chat_doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient')->constrained('users')->cascadeOnDelete();
            $table->foreignId('doctor')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(OnlineConsultation::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_doctors');
    }
};
