<?php

namespace App\Http\Controllers\User\Pharmacy;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\pharmacy\Category\CategoryService;
use Illuminate\Http\JsonResponse;
class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        return $this->categoryService->getAllCategories();
    }

    public function show($id)
    {
        return $this->categoryService->getCategoryById($id);
    }
}
