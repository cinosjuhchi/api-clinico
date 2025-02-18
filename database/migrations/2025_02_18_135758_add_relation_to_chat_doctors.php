<?php

use App\Models\Patient;
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
        Schema::table('chat_doctors', function (Blueprint $table) {
            if (Schema::hasColumn('chat_doctors', 'patient')) {
                $table->dropForeign(['patient']);
                $table->dropColumn('patient');
            }
            if (Schema::hasColumn('chat_doctors', 'doctor')) {
                $table->dropForeign(['doctor']);
                $table->dropColumn('doctor');
            }
            
            if (!Schema::hasColumn('chat_doctors', 'sender_id')) {
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            }

            if (!Schema::hasColumn('chat_doctors', 'receiver_id')) {
                $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_doctors', function (Blueprint $table) {
            //
        });
    }
};
