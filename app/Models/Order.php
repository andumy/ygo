<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int id
 * @property string name
 * @property OwnedCard[] ownedCards
 */
class Order extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public function ownedCards(): HasMany
    {
        return $this->hasMany(OwnedCard::class);
    }

    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, OwnedCard::class);
    }
}
