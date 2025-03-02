<?php

namespace App\Http\Controllers\User;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image ? url(Storage::url($category->image)) : null,
            ];
        });

        return response()->json($categories, 200);
    }

    public function show($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'image' => $category->image ? url(Storage::url($category->image)) : null,
            'products' => $category->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'status' => $product->status,
                    'main_image_url' => $product->image ? url(Storage::url($product->image)) : null,
                ];
            }),
        ], 200);
    }
}
