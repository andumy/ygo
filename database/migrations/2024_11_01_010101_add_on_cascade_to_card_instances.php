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
        Schema::table('card_instances', function (Blueprint $table) {
            $table->dropForeign('card_instances_card_id_foreign');
            $table->foreign('card_id')->references('id')->on('cards')
                ->cascadeOnDelete();

            $table->dropForeign('card_instances_set_id_foreign');
            $table->foreign('set_id')->references('id')->on('sets')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_instances', function (Blueprint $table) {
            $table->dropForeign('card_instances_card_id_foreign');
            $table->foreign('card_id')->references('id')->on('cards');

            $table->dropForeign('card_instances_set_id_foreign');
            $table->foreign('set_id')->references('id')->on('sets');
        });
    }
};
