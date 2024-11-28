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
        Schema::table('medication_records', function (Blueprint $table) {
            $table->decimal('total_cost', 8, 2)->nullable();
            $table->integer('qty')->nullable();
            $table->foreignId('medication_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medication_records', function (Blueprint $table) {
            //
        });
    }
};
