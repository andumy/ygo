<?php

use App\Enums\Games;
use App\Models\Game;
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
        $defaultGame = Game::where('name', Games::YGO)->first()->id;

        Schema::table("cards", function(Blueprint $table) use ($defaultGame){
            $table->foreignId("game_id")->default($defaultGame)->constrained()->cascadeOnDelete();
            $table->unique(['id', 'game_id']);
        });

        Schema::table("sets", function(Blueprint $table) use ($defaultGame){
            $table->foreignId("game_id")->default($defaultGame)->constrained()->cascadeOnDelete();
            $table->unique(['id', 'game_id']);
        });

        Schema::table("card_instances", function(Blueprint $table) use ($defaultGame){
            $table->foreignId("game_id")->default($defaultGame)->constrained()->cascadeOnDelete();
            $table->dropForeign('card_instances_card_id_foreign');
            $table->dropForeign('card_instances_set_id_foreign');
            $table->foreign(['card_id', 'game_id'])->references(['id', 'game_id'])->on('cards')->onDelete('cascade');
            $table->foreign(['set_id', 'game_id'])->references(['id', 'game_id'])->on('sets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("cards", function(Blueprint $table){
            $table->dropForeign(['game_id']);
            $table->dropColumn('game_id');
        });

        Schema::table("sets", function(Blueprint $table){
            $table->dropForeign(['game_id']);
            $table->dropColumn('game_id');
        });

        Schema::table("card_instances", function(Blueprint $table){
            $table->dropForeign(['game_id']);
            $table->dropForeign(['card_id', 'game_id']);
            $table->dropForeign(['set_id', 'game_id']);
            $table->dropColumn('game_id');
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
            $table->foreign('set_id')->references('id')->on('sets')->onDelete('cascade');
        });
    }
};
