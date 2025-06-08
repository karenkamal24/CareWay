<?php

namespace App\Services\Pharmacy;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponseHelper;

class CategoryService
{
 // Get all categories
 public function getAllCategories()
 {
     try {
         $categories = Category::all()->map(function ($category) {
             return [
                 'id' => $category->id,
                 'name' => $category->name,
                 'image' => $category->image ? url(Storage::url($category->image)) : null,
             ];
         });

         return ApiResponseHelper::success('Categories retrieved successfully', $categories);

     } catch (\Throwable $e) {
         return ApiResponseHelper::error('Failed to retrieve categories');
     }
 }


 public function getCategoryById($id)
 {
     try {
         $category = Category::with('products')->find($id);

         if (!$category) {
             return ApiResponseHelper::notFound('Category not found');
         }

         $data = [
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
         ];

         return ApiResponseHelper::success('Category details retrieved', $data);

     } catch (\Throwable $e) {
         return ApiResponseHelper::error('Failed to retrieve category');
     }
 }

}
