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
            $table->dropForeign('owned_cards_card_instance_id_foreign');
            $table->foreign('card_instance_id')->references('id')->on('card_instances')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owned_cards', function (Blueprint $table) {
            $table->dropForeign('owned_cards_card_instance_id_foreign');
            $table->foreign('card_instance_id')->references('id')->on('card_instances');
        });
    }
};
