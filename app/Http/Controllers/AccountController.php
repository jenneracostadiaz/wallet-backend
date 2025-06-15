<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $accounts = auth()->user()
            ->accounts()
            ->with('currency')
            ->orderBy('order')
            ->get();

        return AccountResource::collection($accounts);
    }

    public function show(Account $account): AccountResource
    {
        $this->authorize('view', $account);

        return new AccountResource($account->load('currency'));
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = auth()->user()->accounts()->create([
            ...$request->validated(),
            'order' => $this->getNextOrder(),
        ]);

        return (new AccountResource($account->load('currency')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $this->authorize('update', $account);

        $account->update($request->validated());

        return new AccountResource($account->load('currency'));
    }

    public function destroy(Account $account): JsonResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }

    private function getNextOrder(): int
    {
        return auth()->user()->accounts()->max('order') + 1;
    }
}
