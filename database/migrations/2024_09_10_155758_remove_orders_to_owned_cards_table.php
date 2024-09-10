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
            $table->dropForeign('owned_cards_order_id_foreign');
            $table->dropColumn('order_id');
            $table->dropColumn('order_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owned_cards', function (Blueprint $table) {
            $table->integer('order_amount')->nullable()->default(null);
            $table->foreignId('order_id')->nullable()->default(null)->constrained();
        });
    }
};
