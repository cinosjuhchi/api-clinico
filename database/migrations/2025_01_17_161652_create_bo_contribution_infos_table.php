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
        Schema::create('bo_contribution_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('kwsp_number');
            $table->decimal('kwsp_amount', 8, 2);
            $table->bigInteger('perkeso_number');
            $table->decimal('perkeso_amount', 8, 2);
            $table->string('tax_number');
            $table->decimal('tax_amount', 8, 2);
            $table->foreignId('admin_clinico_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bo_contribution_infos');
    }
};
