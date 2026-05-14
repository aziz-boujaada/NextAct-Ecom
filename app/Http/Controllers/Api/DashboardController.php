<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {
          $this->middleware('permissions:view_dashboard')->only(['index']);
    }

    public function index()
    {

        

        return response()->json([
            'status' => 'success',
            'data' => $this->dashboardService->stats(),
        ]);
    }
}
