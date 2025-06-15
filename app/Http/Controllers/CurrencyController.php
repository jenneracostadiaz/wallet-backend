<?php

namespace App\Http\Controllers;

use App\Http\Resources\CurrencyResource;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();

        return CurrencyResource::collection($currencies);
    }

    public function show(Currency $currency)
    {
        return new CurrencyResource($currency);
    }
}
