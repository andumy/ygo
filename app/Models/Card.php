<?php

namespace App\Models;

use App\Enums\Games;
use App\Repositories\GameRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int id
 * @property int passcode
 * @property int card_id
 * @property string name
 * @property string alias
 * @property string type
 * @property bool has_image
 * @property int game_id
 * @property Game game
 * @property Collection<Variant> variants
 * @property Collection<VariantCard> variantCards
 * @property Collection<CardInstance> cardInstances
 * @property Collection<Set> sets
 * @property boolean isOwned
 * @property boolean isOrdered
 * @property boolean isMissing
 * @property Carbon last_price_fetch
 */
class Card extends Model
{
    use HasFactory;
    use HasRelationships;

    public $timestamps = false;
    protected $guarded = [];

    public $casts = [
        'last_price_fetch' => 'date',
        'has_image' => 'boolean',
    ];

    public function cardInstances(): HasMany
    {
        return $this->hasMany(CardInstance::class);
    }

    public function sets(): BelongsToMany
    {
        return $this->belongsToMany(
            Set::class,
            'card_instances',
            'card_id',
            'set_id'
        );
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function codeForSet(string $set): string {
        return $this->cardInstances()->whereHas('set', fn ($q) => $q->where('name', $set))
            ->first()->card_set_code ?? '';
    }

    public function variants(): HasManyThrough
    {
        return $this->hasManyThrough(Variant::class, CardInstance::class);
    }

    public function variantCards(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->variants(), (new Variant())->variantCard());
    }

    public function getVariantCardsAttribute(): Collection
    {
        return $this->variantCards()->distinct()->get();
    }

    public function getIsOwnedAttribute(): bool
    {
        return $this->cardInstances->filter(function (CardInstance $instance) {
            return $instance->isOwned;
        })->isNotEmpty();
    }
    public function getIsOrderedAttribute(): bool
    {
        return $this->cardInstances->filter(function (CardInstance $instance) {
            return $instance->isOrdered;
        })->isNotEmpty() && !$this->isOwned;
    }

    public function getIsMissingAttribute(): bool
    {
        return !$this->isOwned && !$this->isOrdered;
    }

    public function getPasscodeAttribute(): string
    {
        /** @var VariantCard $variantCard */
        $variantCard = $this->variantCards()->where('is_original', 1)->first();
        return $variantCard->passcode;
    }

    protected static function booted(): void
    {
        static::addGlobalScope('game', function (Builder $builder) {

            $builder->where(
                'cards.game_id',
        Session::get('game_id') ?? Games::YGO->id()
            );
        });
    }
}
