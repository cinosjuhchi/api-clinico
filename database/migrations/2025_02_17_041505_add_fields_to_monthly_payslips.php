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
            $table->decimal('overtime');
            $table->decimal('kwsp_employer');
            $table->decimal('perkeso_employer');
            $table->decimal('eis_employer');
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
