<?php

use App\Models\LeaveType;
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
        Schema::create('leave_permissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('clinic_id');
            $table->foreignIdFor(LeaveType::class)->constrained()->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->text('reason');
            $table->string('attachment');
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_permissions');
    }
};
