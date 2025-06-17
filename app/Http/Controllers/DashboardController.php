<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $dashboardService = new DashboardService($request->user()->id);
        $data = $dashboardService->getDashboardData();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Dashboard data retrieved successfully',
        ]);
    }

    public function balance(Request $request): JsonResponse
    {
        $dashboardService = new DashboardService($request->user()->id);
        $balance = $dashboardService->getCurrentTotalBalance();

        return response()->json([
            'success' => true,
            'data' => $balance,
        ]);
    }
}
