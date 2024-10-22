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
        Schema::create('injections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand');
            $table->string('sku_code');
            $table->integer('paediatric_dose');
            $table->string('unit');
            $table->bigInteger('batch');
            $table->date('expired_date');
            $table->bigInteger('total_amount');
            $table->decimal('price', 8, 2);            
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pregnancy_category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('injections');
    }
};
