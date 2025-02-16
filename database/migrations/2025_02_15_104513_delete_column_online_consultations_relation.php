<?php

use App\Models\ChatDoctorBill;
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
        Schema::table('online_consultations', function (Blueprint $table) {
            $table->dropForeignIdFor(ChatDoctorBill::class);
            $table->dropColumn('is_confirmed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_consultations', function (Blueprint $table) {
            //
        });
    }
};
