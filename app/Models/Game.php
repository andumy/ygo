<?php

namespace App\Models;

use App\Enums\Games;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property Games $name
 * @property Collection<Card> $cards
 * @property Collection<Set> $sets
 * @property Collection<CardInstance> $cardInstances
 */
class Game extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;
    protected $casts = [
        'name' => Games::class,
    ];

    public function cards(): HasMany{
        return $this->hasMany(Card::class);
    }

    public function sets(): HasMany{
        return $this->hasMany(Set::class);
    }

    public function cardInstances(): HasMany{
        return $this->hasMany(CardInstance::class);
    }
}
