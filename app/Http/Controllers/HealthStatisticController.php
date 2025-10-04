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
 public function indexMedicalArticles()
{
    $articles = $this->homepageService->getAll();

    return response()->json([
        'status'  => 'success',
        'message' => 'Medical Articles fetched successfully',
        'data'    => $articles,
    ]);
}

  public function indexGallery()
    {
        $images = $this->homepageService->getAllGalleryImage();

        return response()->json([
            'status'  => 'success',
            'message' => 'Gallery images fetched successfully',
            'data'    => $images,
        ]);
    }
}
