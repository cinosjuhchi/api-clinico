<?php

use App\Models\ReportBugType;
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
        Schema::create('report_bugs', function (Blueprint $table) {
            $table->id();
            $table->string("email");
            $table->text("note");
            $table->foreignIdFor(ReportBugType::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_bugs');
    }
};
