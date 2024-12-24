<?php

use App\Models\ClaimItem;
use App\Models\Clinic;
use App\Models\User;
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
        Schema::create('claim_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->integer('month'); // 1 sampai 12
            $table->foreignIdFor(Clinic::class)->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->string('attachment');
            $table->foreignIdFor(ClaimItem::class)->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_permissions');
    }
};
