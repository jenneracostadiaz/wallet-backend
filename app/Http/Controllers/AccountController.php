<?php

namespace App\Http\Controllers;

class AccountController extends Controller
{
    public function index()
    {
        try {
            $accounts = auth()->user()->accounts()->with('currency')->get();

            return response()->json($accounts);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving accounts', 'message' => $e->getMessage()], 500);
        }
    }
}
