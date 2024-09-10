<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
