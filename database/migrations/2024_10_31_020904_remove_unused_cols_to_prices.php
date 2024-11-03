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
        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('low');
            $table->dropColumn('high');
            $table->dropColumn('avg');
            $table->dropColumn('market');
            $table->float('price')->after('card_instance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->float('low');
            $table->float('high');
            $table->float('avg');
            $table->float('market')->nullable()->after('avg');
            $table->dropColumn('price');
        });
    }
};
