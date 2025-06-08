<?php
namespace App\Services\Pharmacy;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class MedicineService
{
    public function getAllMedicines(): array
    {
        $products = Product::all()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'status' => $product->status,
                'main_image_url' => $product->image ? url(Storage::url($product->image)) : null,
            ];
        });

        return $products->toArray();
    }

    public function getMedicineById($id): ?array
    {
        $medicine = Product::find($id);

        if (!$medicine) {
            return null;
        }

        return [
            'id' => $medicine->id,
            'name' => $medicine->name,
            'description' => $medicine->description,
            'price' => $medicine->price,
            'quantity' => $medicine->quantity,
            'status' => $medicine->status,
            'main_image_url' => $medicine->image ? url(Storage::url($medicine->image)) : null,
        ];
    }
}
