<?php

namespace App\Http\Controllers\User\Pharmacy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Pharmacy\CategoryService;
use App\Helpers\ApiResponseHelper;
use App\Http\Resources\ProductResource;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // GET /categories/main
    public function main()
    {
        return $this->categoryService->getMainCategories();
    }

    // GET /categories/{id}/subcategories
    public function subcategories($id)
    {
        return $this->categoryService->getSubcategoriesByParent($id);
    }

    // GET /categories/{id}/products?subcategory_id=xx&limit=xx&page=xx
public function products(Request $request, $id)
{
    $subcategoryId = $request->get('subcategory_id');
    return $this->categoryService->getProductsByCategory($id, $subcategoryId);
}

}
