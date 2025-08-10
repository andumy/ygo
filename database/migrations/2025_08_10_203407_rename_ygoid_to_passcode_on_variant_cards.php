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
        Schema::table('variant_cards', function (Blueprint $table) {
            $table->renameColumn('ygo_id', 'passcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variant_cards', function (Blueprint $table) {
            $table->renameColumn('passcode', 'ygo_id');
        });
    }
};
