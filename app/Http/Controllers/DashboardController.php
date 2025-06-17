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

    public function monthlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $dashboardService = new DashboardService($request->user()->id);
        $summary = $dashboardService->getMonthlyBasicSummary($request->month);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);

    }

    public function latestTransactions(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $dashboardService = new DashboardService($request->user()->id);
        $transactions = $dashboardService->getLatestTransactions($request->limit ?? 10);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function monthlyComparison(Request $request): JsonResponse
    {
        $dashboardService = new DashboardService($request->user()->id);
        $comparison = $dashboardService->getMonthlyComparison();

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    public function quickStats(Request $request): JsonResponse
    {
        $dashboardService = new DashboardService($request->user()->id);
        $stats = $dashboardService->getQuickStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
