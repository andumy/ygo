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
        Schema::table('variants', function (Blueprint $table) {
            $table->dropForeign(['card_instance_id']);
            $table->dropUnique('card_instance_id_ygo_id_unique');
            $table->dropColumn(['is_original', 'ygo_id']);
            $table->foreign(['card_instance_id'])->references('id') ->on('card_instances');
            $table->unique(['card_instance_id', 'variant_card_id'], 'card_instance_id_variant_card_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropUnique('card_instance_id_variant_card_id_unique');
            $table->boolean('is_original')->default(false);
            $table->string('ygo_id')->nullable();
            $table->unique(['card_instance_id', 'ygo_id'], 'card_instance_id_ygo_id_unique');

        });
    }
};
