<?php

use App\Models\ChatDoctorBill;
use App\Models\OnlineConsultation;
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
        Schema::table('online_consultations', function (Blueprint $table) {
            if (Schema::hasColumn('online_consultations', 'online_consultation_id')) {
               // Drop foreign key constraint terlebih dahulu
                $table->dropForeign(['online_consultation_id']);
                // Drop kolom 'chat_doctor_bill_id'
                $table->dropColumn('online_consultation_id');
            }

            $table->foreignIdFor(ChatDoctorBill::class)->constrained()->cascadeOnDelete();
            
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
