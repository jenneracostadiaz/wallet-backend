<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

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

    public function show(Account $account)
    {
        try {
            if ($account->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json($account->load('currency'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving account', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:checking,savings,credit_card,cash',
                'balance' => 'required|numeric|min:0',
                'currency_id' => 'required|exists:currencies,id',
                'description' => 'nullable|string|max:1000',
            ]);

            $order = auth()->user()->accounts()->max('order') + 1;

            $account = auth()->user()->accounts()->create([
                'name' => $request->name,
                'type' => $request->type,
                'balance' => $request->balance,
                'currency_id' => $request->currency_id,
                'description' => $request->description,
                'order' => $order,
            ]);

            return response()->json($account, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating account', 'message' => $e->getMessage()], 500);
        }
    }
}
