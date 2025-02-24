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
        Schema::table('monthly_payslips', function (Blueprint $table) {
            $table->decimal('total_kwsp');
            $table->decimal('total_perkeso');
            $table->decimal('total_eis');
            $table->string('company');
            $table->string('name');
            $table->string('department');
            $table->string('staff_id');
            $table->string('nric');
            $table->string('bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_payslips', function (Blueprint $table) {
            //
        });
    }
};
