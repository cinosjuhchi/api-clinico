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
        Schema::create('bo_invoices', function (Blueprint $table) {
            $table->id();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('clinic_name');
            $table->string('clinic_email');
            $table->string('clinic_phone_number');
            $table->string('clinic_address');
            $table->enum('status', ['pending', 'completed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bo_invoices');
    }
};
