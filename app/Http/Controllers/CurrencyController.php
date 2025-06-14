<?php

namespace App\Http\Controllers;

use App\Models\Currency;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $currencies = Currency::all();

            return response()->json($currencies);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving currencies', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Currency $currency)
    {
        try {
            return response()->json($currency);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving currency', 'message' => $e->getMessage()], 500);
        }
    }
}
