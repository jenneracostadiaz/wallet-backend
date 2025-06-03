<?php

namespace App\Http\Controllers;

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
            'accounts' => auth()->user()->accounts()->get()
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'currency' => 'required',
            'balance' => 'required',
            'description' => 'required',
        ]);

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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
