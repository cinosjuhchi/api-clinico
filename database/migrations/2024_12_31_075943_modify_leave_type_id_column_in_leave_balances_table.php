<?php

use App\Models\LeaveType;
use App\Models\LeaveTypeDetail;
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
        Schema::table('leave_balances', function (Blueprint $table) {
            // $table->dropForeign(['leave_type_id']);
            // $table->dropColumn('leave_type_id');
            $table->foreignIdFor(LeaveTypeDetail::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropForeign(['leave_type_detail_id']);
            $table->dropColumn('leave_type_detail_id');
            $table->foreignIdFor(LeaveType::class)->constrained()->cascadeOnDelete();
        });
    }
};
