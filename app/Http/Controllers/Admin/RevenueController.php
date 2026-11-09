<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class RevenueController extends Controller
{
    public function __invoke(RevenueService $revenue): JsonResponse
    {
        return response()->json(Arr::only($revenue->dashboardData(), ['today', 'daily', 'monthly']));
    }
}
