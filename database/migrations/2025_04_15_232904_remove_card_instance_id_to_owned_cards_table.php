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
            $table->dropForeign(['card_instance_id']);
            $table->dropColumn('card_instance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owned_cards', function (Blueprint $table) {
            $table->foreignId('card_instance_id')
                ->constrained('card_instances');
        });
    }
};
