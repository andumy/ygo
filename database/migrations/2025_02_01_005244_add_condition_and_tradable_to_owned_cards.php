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
        Schema::table('owned_cards', function (Blueprint $table) {
            $table->enum('cond', [
                'MINT',
                'NEAR MINT',
                'EXCELLENT',
                'GOOD',
                'LIGHT PLAYED',
                'PLAYED',
                'POOR',
            ])->default('NEAR MINT');
            $table->enum('sale', [
                'NOT SET',
                'TRADE',
                'IN COLLECTION'
            ])->default('NOT SET');
            $table->boolean('is_first_edition')->default(false);
            $table->integer('batch')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owned_cards', function (Blueprint $table) {
            $table->dropColumn('cond');
            $table->dropColumn('sale');
            $table->dropColumn('is_first_edition');
            $table->dropColumn('batch');
        });
    }
};
