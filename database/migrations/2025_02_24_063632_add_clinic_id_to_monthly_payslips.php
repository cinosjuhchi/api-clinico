<?php

use App\Models\Clinic;
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
            $table->foreignIdFor(Clinic::class)->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('eis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_payslips', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Clinic::class);
            $table->dropColumn("eis");
        });
    }
};
