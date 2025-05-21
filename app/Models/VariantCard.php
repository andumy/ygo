<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int id
 * @property string ygo_id
 * @property bool is_original
 * @property Collection<Variant> variants
 * @property Collection<Variant> variantsOrderedByCode
 * @property Collection<CardInstance> cardInstances
 * @property boolean isOwned
 * @property boolean isOrdered
 * @property boolean isMissing
 */
class VariantCard extends Model
{
    use HasFactory;
    use HasRelationships;

    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'is_original' => 'boolean',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function cardInstances(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->variants(), (new Variant())->cardInstance());
    }

    public function getCardInstancesAttribute(): Collection
    {
        return $this->cardInstances()->distinct()->get();
    }

    public function getIsOwnedAttribute(): bool
    {
        return $this->variants->filter(function (Variant $variant) {
            return $variant->isOwned;
        })->isNotEmpty();
    }
    public function getIsOrderedAttribute(): bool
    {
        return $this->variants->filter(function (Variant $variant) {
                return $variant->isOrdered;
        })->isNotEmpty() && !$this->isOwned;
    }

    public function getIsMissingAttribute(): bool
    {
        return !$this->isOwned && !$this->isOrdered;
    }

    /** @return Collection<Variant> */
    public function getVariantsOrderedByCodeAttribute(): Collection
    {
        return $this->variants()->join('card_instances', 'card_instances.id', '=', 'variants.card_instance_id')
            ->select('variants.*')
            ->orderBy('card_instances.card_set_code')
            ->get();
    }
}
