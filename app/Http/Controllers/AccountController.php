<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        return $this->handleExceptions(function () {
            $accounts = $this->user()->accounts()->with('currency')->get();
            return response()->json($accounts);
        }, 'Error retrieving accounts');
    }

    public function show(Account $account)
    {
        return $this->handleExceptions(function () use ($account) {
            $this->authorizeAccount($account);
            return response()->json($account->load('currency'));
        }, 'Error retrieving account');
    }

    public function store(Request $request)
    {
        return $this->handleExceptions(function () use ($request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:checking,savings,credit_card,cash',
                'balance' => 'required|numeric|min:0',
                'currency_id' => 'required|exists:currencies,id',
                'description' => 'nullable|string|max:1000',
            ]);

            $order = $this->user()->accounts()->max('order') + 1;

            $account = $this->user()->accounts()->create([
                'name' => $request->name,
                'type' => $request->type,
                'balance' => $request->balance,
                'currency_id' => $request->currency_id,
                'description' => $request->description,
                'order' => $order,
            ]);

            return response()->json($account, 201);
        }, 'Error creating account');
    }

    public function update(Request $request, Account $account)
    {
        return $this->handleExceptions(function () use ($request, $account) {
            $this->authorizeAccount($account);
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:checking,savings,credit_card,cash',
                'balance' => 'required|numeric|min:0',
                'currency_id' => 'required|exists:currencies,id',
                'description' => 'nullable|string|max:1000',
            ]);
            $account->update($request->all());
            return response()->json($account);
        }, 'Error updating account');
    }

    public function destroy(Account $account)
    {
        return $this->handleExceptions(function () use ($account) {
            $this->authorizeAccount($account);
            $account->delete();
            return response()->json(['message' => 'Account deleted successfully']);
        }, 'Error deleting account');
    }

    /**
     * Métodos privados auxiliares
     */
    private function user()
    {
        return auth()->user();
    }

    private function authorizeAccount(Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            abort(response()->json(['error' => 'Unauthorized'], 403));
        }
    }

    private function handleExceptions(\Closure $callback, $errorMessage)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            return response()->json([
                'error' => $errorMessage,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
