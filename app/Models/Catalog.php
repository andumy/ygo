<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $cardmarket_id
 * @property string $name
 * @property string|null $number
 * @property string|null $rarity
 * @property string|null $expansion
 * @property string|null $expansion_code
 */
class Catalog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;
}
