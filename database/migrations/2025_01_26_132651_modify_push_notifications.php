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
        Schema::table('push_notifications', function (Blueprint $table) {
            $table->text('endpoint')->nullable()->change();
            $table->text('p256dh')->nullable()->change();
            $table->text('auth')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_notifications', function (Blueprint $table) {
            //
        });
    }
};
