<?php

namespace App\Services\Pharmacy;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponseHelper;

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

public function getProductsByCategory($id, $subcategoryId = null, $perPage = 10)
{
    $query = Product::where('category_id', $id);

    if ($subcategoryId) {
        $query->where('subcategory_id', $subcategoryId);
    }

    return $query->paginate($perPage);
}

}
