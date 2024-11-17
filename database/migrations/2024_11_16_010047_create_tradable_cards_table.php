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
        Schema::create('tradable_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_instance_id')->constrained();
            $table->integer('collectable_amount');
            $table->integer('tradable_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tradable_cards');
    }
};
