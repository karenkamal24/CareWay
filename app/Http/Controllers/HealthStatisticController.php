<?php

namespace App\Http\Controllers;

use App\Services\HomepageService;
use Illuminate\Http\Request;

class HealthStatisticController extends Controller
{
    protected $homepageService;

    public function __construct(HomepageService $homepageService)
    {
        $this->homepageService = $homepageService;
    }

    public function index()
    {
        $statistics = $this->homepageService->getHealthStatistics();

        return response()->json([
            'status' => 'success',
            'message' => 'Health statistics fetched successfully',
            'data' => $statistics
        ]);
    }
}
