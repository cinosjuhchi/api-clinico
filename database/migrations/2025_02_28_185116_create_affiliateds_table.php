<?php

use App\Models\Referral;
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
        Schema::create('affiliateds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Referral::class)->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->enum('status', ['pending', 'paid']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliateds');
    }
};
