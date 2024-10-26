<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int id
 * @property string name
 * @property OrderedCard[] orderedCards
 */
class Order extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public function orderedCards(): HasMany
    {
        return $this->hasMany(OrderedCard::class);
    }

    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, OrderedCard::class);
    }
}
