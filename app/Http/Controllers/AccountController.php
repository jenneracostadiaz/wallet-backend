<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        try {
            $accounts = auth()->user()->accounts()->with('currency')->get();

            return AccountResource::collection($accounts);
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

    public function store(StoreAccountRequest $request)
    {
        try {
            $order = auth()->user()->accounts()->max('order') + 1;

            $account = auth()->user()->accounts()->create([
                'name' => $request->name,
                'type' => $request->type,
                'balance' => $request->balance,
                'currency_id' => $request->currency_id,
                'description' => $request->description,
                'order' => $order,
            ]);

            return (new AccountResource($account))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating account', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        try {
            if ($account->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $account->update($request->all());
            $account->load('currency');

            return new AccountResource($account);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating account', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Account $account)
    {
        try {
            if ($account->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $account->delete();

            return response()->json(['message' => 'Account deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting account', 'message' => $e->getMessage()], 500);
        }
    }
}
