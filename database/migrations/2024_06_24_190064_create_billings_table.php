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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->string('billz_id')->nullable();
            $table->dateTime('transaction_date');
            $table->decimal('total_cost', 12, 2);
            $table->enum('type', ['cash', 'clinico', 'panel'])->default('clinico');
            $table->boolean('is_paid')->default(false);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();            
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
