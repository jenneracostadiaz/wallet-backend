<?php

namespace App\Models;

use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'is_active',
    ];

    public $timestamps = false;

    protected $casts = [
        'decimal_places' => 'integer',
        'is_active' => 'boolean',
    ];
}
