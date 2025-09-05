<?php

namespace App\Http\Controllers\User\Pharmacy;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Services\Pharmacy\MedicineService;
use App\Helpers\ApiResponseHelper;
use App\Http\Resources\ProductResource;
class MedicineController extends Controller
{
    protected MedicineService $medicineService;

    public function __construct(MedicineService $medicineService)
    {
        $this->medicineService = $medicineService;
    }

public function index()
{
    $products = $this->medicineService->getAllMedicines();

    return ApiResponseHelper::success(
        'Medicines retrieved successfully',
        ProductResource::collection($products)
    );
}

public function show($id)
{
    $medicine = $this->medicineService->getMedicineById($id);

    if (!$medicine) {
        return ApiResponseHelper::notFound('Medicine not found');
    }

    return ApiResponseHelper::success(
        'Medicine retrieved successfully',
        new ProductResource($medicine)
    );
}
public function latest()
{
    $products = Product::orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Latest products retrieved successfully',
        'data' => ProductResource::collection($products),
    ], 200, [], JSON_UNESCAPED_UNICODE);
}


}
