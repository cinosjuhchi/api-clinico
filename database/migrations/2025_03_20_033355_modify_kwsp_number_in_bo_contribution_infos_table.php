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
        Schema::table('bo_contribution_infos', function (Blueprint $table) {
            $table->string('kwsp_number')->change();
            $table->string('perkeso_number')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bo_contribution_infos', function (Blueprint $table) {
            $table->string('kwsp_number')->change();
            $table->string('perkeso_number')->change();
        });
    }
};
