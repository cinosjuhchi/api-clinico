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
        Schema::table('clinic_settlements', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('settlement_date'); // File (Iimage or PDF)
            $table->enum('status', ['pending', 'checking', 'declined', 'completed'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_settlements', function (Blueprint $table) {
            $table->dropColumn('payment_proof');
            $table->enum('status', ['pending', 'completed'])->default('pending')->change();
        });
    }
};
