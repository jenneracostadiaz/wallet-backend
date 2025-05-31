<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Account extends Controller
{
    public function index()
    {
        return Inertia::render('accounts/index',[
            'accounts' => auth()->user()->accounts
        ]);
    }
}
