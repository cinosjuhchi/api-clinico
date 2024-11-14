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
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'consultation', 'on-consultation', 'take-medicine', 'completed', 'cancelled', 'waiting-payment'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Define the original column type in down() if you need to rollback
        Schema::table('appointments', function (Blueprint $table) {
            // Replace with your original column definition
            $table->string('status')->change();
        });
    }
};
