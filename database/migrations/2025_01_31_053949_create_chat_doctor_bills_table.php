<?php

use App\Models\ChatDoctor;
use App\Models\OnlineConsultation;
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
        Schema::create('chat_doctor_bills', function (Blueprint $table) {
            $table->id();
            $table->string('billz_id')->nullable();
            $table->dateTime('transaction_date');
            $table->decimal('total_cost', 12, 2);            
            $table->boolean('is_paid')->default(false);
            $table->foreignIdFor(OnlineConsultation::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_doctor_bills');
    }
};
