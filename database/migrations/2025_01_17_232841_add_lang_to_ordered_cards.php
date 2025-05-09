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
        Schema::table('ordered_cards', function (Blueprint $table) {
            $table->enum('lang', ['EN', 'FR', 'DE', 'IT', 'PT', 'SP', 'JP', 'JA', 'AE', 'KR', 'TC', 'SC'])->default('en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordered_cards', function (Blueprint $table) {
            $table->dropColumn('lang');
        });
    }
};
