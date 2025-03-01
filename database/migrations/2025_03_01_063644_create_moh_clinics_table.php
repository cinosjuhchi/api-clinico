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
        Schema::create('moh_clinics', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_number_phone');
            $table->string('head_departement');
            $table->integer('post_code');
            $table->string('state');
            $table->foreignIdFor(Clinic::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moh_clinics');
    }
};
