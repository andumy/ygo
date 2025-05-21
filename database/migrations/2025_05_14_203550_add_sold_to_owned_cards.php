<?php

use App\Enums\Sale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE owned_cards MODIFY COLUMN sale
                ENUM(
                    '".Sale::NOT_SET->value."',
                    '".Sale::TRADE->value."',
                    '".Sale::IN_COLLECTION->value."',
                    '".Sale::LISTED->value."',
                    '".Sale::SOLD->value."'
                ) DEFAULT '".Sale::NOT_SET->value."'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE owned_cards MODIFY COLUMN sale
                ENUM(
                    '".Sale::NOT_SET->value."',
                    '".Sale::TRADE->value."',
                    '".Sale::IN_COLLECTION->value."',
                    '".Sale::LISTED->value."'
                ) DEFAULT '".Sale::NOT_SET->value."'"
        );
    }
};
