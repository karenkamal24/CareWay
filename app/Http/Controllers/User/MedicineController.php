<?php

namespace App\Http\Controllers\User;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class MedicineController extends Controller
{
    public function index()
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

        return response()->json($products, 200);
    }

    public function show($id)
    {
        $medicine = Product::find($id);

        if (!$medicine) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }

        return response()->json([
            'id' => $medicine->id,
            'name' => $medicine->name,
            'description' => $medicine->description,
            'price' => $medicine->price,
            'quantity' => $medicine->quantity,
            'status' => $medicine->status,
            'main_image_url' => $medicine->image ? url(Storage::url($medicine->image)) : null,
        ], 200);
    }

}
