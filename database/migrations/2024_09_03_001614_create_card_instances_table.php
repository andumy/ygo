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
        Schema::create('card_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained();
            $table->foreignId('set_id')->constrained();
            $table->string('card_set_code');
            $table->string('rarity_verbose');
            $table->string('rarity_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_set');
    }
};
