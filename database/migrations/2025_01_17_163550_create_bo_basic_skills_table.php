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
        Schema::create('bo_basic_skills', function (Blueprint $table) {
            $table->id();
            $table->string('languange_spoken');
            $table->string('languange_written');
            $table->string('microsoft_office');
            $table->string('others');
            $table->foreignId('admin_clinico_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bo_basic_skills');
    }
};
