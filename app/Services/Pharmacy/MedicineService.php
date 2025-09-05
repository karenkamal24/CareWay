<?php
namespace App\Services\Pharmacy;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class MedicineService
{
public function getAllMedicines()
{
    return Product::all();
}


 public function getMedicineById($id): ?Product
{
    return Product::find($id);
}
}
