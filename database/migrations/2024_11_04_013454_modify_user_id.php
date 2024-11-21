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
        Schema::table('patients', function (Blueprint $table) {
            // First drop the foreign key constraint if it exists
            $table->dropForeign(['user_id']);
            // Then modify the column
            $table->foreignId('user_id')->nullable()->change();            
            // Add the foreign key constraint back
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {            
            $table->dropForeign(['user_id']);
            $table->dropColumn('is_offline');
            $table->foreignId('user_id')->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }
};
