<?php

use App\Enums\OrderCardStatuses;
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
        Schema::create('ordered_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_instance_id')->constrained();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
                OrderCardStatuses::ORDERED->value,
                OrderCardStatuses::DELIVERED->value,
                OrderCardStatuses::CANCELED->value,
            ])->default(OrderCardStatuses::ORDERED->value);
            $table->integer('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordered_cards');
    }
};
