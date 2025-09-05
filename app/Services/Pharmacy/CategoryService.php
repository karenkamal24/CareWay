<?php

namespace App\Services\Pharmacy;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponseHelper;
use App\Http\Resources\ProductResource;

class CategoryService
{
    public function getMainCategories()
    {
        try {
            $categories = Category::whereNull('parent_id')->get()->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image ? url(Storage::url($category->image)) : null,
                ];
            });

            return ApiResponseHelper::success('Main categories retrieved successfully', $categories);
        } catch (\Throwable $e) {
            return ApiResponseHelper::error('Failed to retrieve main categories');
        }
    }

    public function getSubcategoriesByParent($parentId)
    {
        try {
            $subcategories = Category::where('parent_id', $parentId)->get()->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,

                ];
            });

            return ApiResponseHelper::success('Subcategories retrieved successfully', $subcategories);
        } catch (\Throwable $e) {
            return ApiResponseHelper::error('Failed to retrieve subcategories');
        }
    }

public function getProductsByCategory($parentId, $subcategoryId = null)
{
    if ($subcategoryId) {
        $categoryIds = [$subcategoryId];
    } else {
        $categoryIds = Category::where('parent_id', $parentId)->pluck('id')->toArray();
        $categoryIds[] = $parentId;
    }

    $products = Product::whereIn('category_id', $categoryIds)->get();

    $data = ProductResource::collection($products);

    return ApiResponseHelper::success('Products retrieved successfully', $data);
}


}
