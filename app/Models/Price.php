<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property float low
 * @property float high
 * @property float avg
 * @property Carbon $date
 * @property int card_instance_id
 * @property CardInstance $cardInstance
 */

class Price extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];
    public $casts = [
        'date' => 'date'
    ];

    public function cardInstance(): BelongsTo
    {
        return $this->belongsTo(CardInstance::class);
    }
}
