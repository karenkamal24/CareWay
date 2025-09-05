<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;

class PrescriptionController extends Controller
{
    /**
     * Search products by an array of medicine names with high accuracy
     */
public function searchMedicines(Request $request)
{
    $request->validate([
        'names' => 'required|array|min:1',
        'names.*' => 'string|min:1|max:255',
    ]);

    $names = $request->input('names');
    $sanitizedNames = array_filter(array_unique(array_map([$this, 'sanitizeUtf8'], $names)));

    if (empty($sanitizedNames)) {
        return response()->json([
            'status' => false,
            'message' => 'No valid medicine names provided'
        ], 400);
    }

    $products = collect();
    $threshold = 70;
    $maxResults = 5;

    foreach ($sanitizedNames as $name) {
        $cleanInput = strtolower($this->cleanMedicineName($name));
        $inputPrefix = substr($cleanInput, 0, 4);

        $results = Product::where('name', 'LIKE', "%{$inputPrefix}%")
            ->get([
                'id',
                'name',
                'description',
                'price',
                'quantity',
                'active_ingredient',
                'status',
                'image'
            ]);

        $matches = [];

        foreach ($results as $product) {
            $cleanProductName = strtolower($this->cleanMedicineName($product->name));
            similar_text($cleanInput, $cleanProductName, $percent);

            if ($percent >= $threshold) {
                $matches[] = [
                    'product' => $product,
                    'percent' => $percent
                ];
            }
        }

        usort($matches, fn($a, $b) => $b['percent'] <=> $a['percent']);
        $matches = array_slice($matches, 0, $maxResults);

        foreach ($matches as $m) {
            $products->push($m['product']);
        }
    }

    $products = $products->unique('name');

    return response()->json([
        'status' => true,
        'data' => ProductResource::collection($products),
        'message' => $products->isEmpty() ? 'No matching products found' : null
    ], 200, [], JSON_UNESCAPED_UNICODE);
}

    /**
     * Sanitize UTF-8 string
     */
    private function sanitizeUtf8($string)
    {
        if (!is_string($string)) return '';
        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $string);
        $string = preg_replace('/[^\P{C}\n]+/u', '', $string);
        return trim(preg_replace('/\s+/u', ' ', $string));
    }


    private function cleanMedicineName($name)
    {
        $name = preg_replace('/^(R\/|\-|\s+)/i', '', $name);
        $name = preg_replace('/\b(tab|mg|ml|gm|mcg)\b/i', '', $name);
        $name = preg_replace('/[^a-zA-Z0-9\s]+/', '', $name);
        return trim(preg_replace('/\s+/', ' ', $name));
    }

public function getByName(Request $request)
{
    $request->validate([
        'name' => 'required|string'
    ]);


    $product = Product::where('name', 'LIKE', "%{$request->name}%")
                      ->first();

    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found'
        ], 404);
    }

  
    $relatedProducts = Product::where('active_ingredient', $product->active_ingredient)
                              ->where('id', '!=', $product->id)
                              ->get();

    return response()->json([
        'status' => true,
        'message' => 'Product fetched successfully',
        'product' => new ProductResource($product),
        'related_products' => ProductResource::collection($relatedProducts),
    ], 200, [], JSON_UNESCAPED_UNICODE);
}

}
