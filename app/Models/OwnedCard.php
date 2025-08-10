<?php

namespace App\Models;

use App\Enums\Condition;
use App\Enums\Games;
use App\Enums\Lang;
use App\Enums\Sale;
use App\Repositories\GameRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * @property int id
 * @property Lang $lang
 * @property Sale $sale
 * @property Condition $cond
 * @property boolean $is_first_edition
 * @property ?int $batch
 * @property ?int $order_id
 * @property Order $order
 * @property Variant $variant
 * @property int $variant_id
 */
class OwnedCard extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'lang' => Lang::class,
        'sale' => Sale::class,
        'cond' => Condition::class,
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
