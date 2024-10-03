<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property string name
 * @property string code
 * @property string alias
 * @property int card_amount
 * @property Carbon date
 * @property Collection<CardInstance> cardInstances
 * @property Collection<Card> cards
 */
class Set extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public $casts = [
        'date' => 'date'
    ];
    public function cardInstances(): HasMany
    {
        return $this->hasMany(CardInstance::class);
    }

    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(
            Card::class,
            'card_instances',
            'set_id',
            'card_id'
        );
    }
}
