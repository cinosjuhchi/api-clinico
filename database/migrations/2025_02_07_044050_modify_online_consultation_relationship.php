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
        Schema::table('online_consultations', function (Blueprint $table) {
            if(Schema::hasColumn('online_consultations', 'expired_at'))
            {
                $table->dropColumn('expired_at');
            }
            $table->foreignId('patient')->constrained('users')->cascadeOnDelete();
            $table->foreignId('doctor')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_confirmed');

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
