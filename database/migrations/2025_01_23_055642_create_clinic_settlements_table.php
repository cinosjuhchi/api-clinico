<?php

use App\Models\Clinic;
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
        Schema::create('clinic_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('clinico_id')->unique()->index();
            $table->decimal('total_sales_cash', 12, 2);
            $table->decimal('total_sales_panel', 12, 2);
            $table->decimal('total_sales_clinico', 12, 2);
            $table->decimal('fee', 12, 2);
            $table->decimal('nett_sales', 12, 2);
            $table->decimal('nett_settlement', 12, 2);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->date('settlement_date');
            $table->foreignIdFor(Clinic::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_settlements');
    }
};
