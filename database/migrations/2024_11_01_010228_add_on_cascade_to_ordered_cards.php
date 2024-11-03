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
            $table->dropForeign('ordered_cards_card_instance_id_foreign');
            $table->foreign('card_instance_id')->references('id')->on('card_instances')
                ->cascadeOnDelete();

            $table->dropForeign('ordered_cards_order_id_foreign');
            $table->foreign('order_id')->references('id')->on('orders')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordered_cards', function (Blueprint $table) {
            $table->dropForeign('ordered_cards_card_instance_id_foreign');
            $table->foreign('card_instance_id')->references('id')->on('card_instances');

            $table->dropForeign('ordered_cards_order_id_foreign');
            $table->foreign('order_id')->references('id')->on('orders');
        });
    }
};
