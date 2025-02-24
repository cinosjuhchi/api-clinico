<?php

use App\Models\User;
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
        Schema::create('monthly_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('claim');
            $table->decimal('hours');
            $table->decimal('sale_incentives')->nullable();
            $table->decimal('kwsp');
            $table->decimal('perkeso');
            $table->decimal('tax');
            $table->decimal('basic_salary', 8, 2);
            $table->decimal('total_earnings',8, 2);
            $table->decimal('total_deduction', 8, 2);
            $table->decimal('nett_salary', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_payslips');
    }
};
