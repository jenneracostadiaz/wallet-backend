<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Account;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('accounts/index',[
            'accounts' => auth()->user()->accounts()->with('currency')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('accounts/create',[
            'currencies' => Currency::all(),
            'types' => [
                [
                    'type' => 'checking',
                    'name' => 'Checking',
                ],
                [
                    'type' => 'savings',
                    'name' => 'Savings',
                ],
                [
                    'type' => 'credit_card',
                    'name' => 'Credit Card',
                ],
                [
                    'type' => 'cash',
                    'name' => 'Cash',
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountRequest $request)
    {

        auth()->user()->accounts()->create([
            'name' => $request->name,
            'type' => $request->type,
            'currency_id' => $request->currency,
            'balance' => $request->balance,
            'description' => $request->description
        ]);
        return redirect()->route('accounts.index')->with('success', 'Account created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        return Inertia::render('accounts/edit',[
            'account' => $account,
            'currencies' => Currency::all(),
            'types' => [
                [
                    'type' => 'checking',
                    'name' => 'Checking',
                ],
                [
                    'type' => 'savings',
                    'name' => 'Savings',
                ],
                [
                    'type' => 'credit_card',
                    'name' => 'Credit Card',
                ],
                [
                    'type' => 'cash',
                    'name' => 'Cash',
                ],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountRequest $request, Account $account)
    {

        $account->update([
            'name' => $request->name,
            'type' => $request->type,
            'currency_id' => $request->currency,
            'balance' => $request->balance,
            'description' => $request->description
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account): RedirectResponse
    {
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Post deleted successfully!');
    }
}
