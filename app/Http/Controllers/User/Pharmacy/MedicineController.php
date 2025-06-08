<?php

namespace App\Http\Controllers\User\Pharmacy;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Services\Pharmacy\MedicineService;
use App\Helpers\ApiResponseHelper;
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

        return ApiResponseHelper::success('Medicines retrieved successfully', $products);
    }

    public function show($id)
    {
        $medicine = $this->medicineService->getMedicineById($id);

        if (!$medicine) {
            return ApiResponseHelper::notFound('Medicine not found');
        }

        return ApiResponseHelper::success('Medicine retrieved successfully', $medicine);
    }

}
