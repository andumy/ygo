<?php

namespace App\Models;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Enums\Sale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property CardInstance cardInstance
 * @property int card_instance_id
 * @property int amount
 * @property Lang $lang
 * @property Sale $sale
 * @property Condition $cond
 * @property boolean $is_first_edition
 * @property ?int $batch
 * @property ?int $order_id
 * @property Order $order
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

    public function cardInstance(): BelongsTo
    {
        return $this->belongsTo(CardInstance::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
