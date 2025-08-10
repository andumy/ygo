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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * @property int id
 * @property string name
 * @property string code
 * @property string alias
 * @property int card_amount
 * @property Carbon date
 * @property Collection<CardInstance> cardInstances
 * @property Collection<Card> cards
 * @property int game_id
 * @property Game game
 */

class Set extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public $casts = [
        'date' => 'date',
    ];
    public function cardInstances(): HasMany
    {
        return $this->hasMany(CardInstance::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
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

    protected static function booted(): void
    {
        static::addGlobalScope('game', function (Builder $builder) {
            /** @var GameRepository $gameRepository */
            $gameRepository = App::make(GameRepository::class);

            $builder->where(
                'sets.game_id',
        Session::get('game_id') ?? $gameRepository->findForGame(Games::YGO)?->id
            );
        });
    }
}
