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

    public function getProductsByCategory($parentId, $subcategoryId = null, $perPage = 10)
    {
        try {
            if ($subcategoryId) {
                $categoryIds = [$subcategoryId];
            } else {
                $categoryIds = Category::where('parent_id', $parentId)->pluck('id')->toArray();
                $categoryIds[] = $parentId;
            }

            $paginator = Product::whereIn('category_id', $categoryIds)
                ->paginate($perPage);

            $data = $paginator->getCollection()->map(function ($product) {
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

            $result = [
                'data' => $data,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];

            return ApiResponseHelper::success('Products retrieved successfully', $result);
        } catch (\Throwable $e) {
            return ApiResponseHelper::error('Failed to retrieve products');
        }
    }
    
}
