<?php

namespace App\Models;

use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property int $decimal_places
 * @property float $exchange_rate_to_pen
 */
class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'exchange_rate_to_pen',
    ];

    public $timestamps = false;

    protected $casts = [
        'decimal_places' => 'integer',
        'exchange_rate_to_pen' => 'float',
    ];
}
