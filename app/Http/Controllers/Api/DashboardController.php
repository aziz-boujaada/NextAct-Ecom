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
        abort_if(auth('api')->user()->role !== User::ROLE_ADMIN, 403, 'Only admins can access dashboard statistics.');

        return response()->json([
            'status' => 'success',
            'data' => $this->dashboardService->stats(),
        ]);
    }
}
