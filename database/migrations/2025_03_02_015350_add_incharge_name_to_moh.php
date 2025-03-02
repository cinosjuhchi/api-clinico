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
        Schema::table('moh_clinics', function (Blueprint $table) {
            $table->string('incharge_name');
            $table->string('incharge_phone_number');
            $table->dropColumn('clinic_number_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moh_clinics', function (Blueprint $table) {
            //
        });
    }
};
