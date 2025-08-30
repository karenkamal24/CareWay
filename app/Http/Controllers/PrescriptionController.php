<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class PrescriptionController extends Controller
{
    /**
     * Search products by an array of medicine names with high accuracy
     */
    public function searchMedicines(Request $request)
    {
        // Validate input
        $request->validate([
            'names' => 'required|array|min:1',
            'names.*' => 'string|min:1|max:255',
        ]);

        $names = $request->input('names');

        // Clean and sanitize input names
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

            // Initial DB filter
            $results = Product::where('name', 'LIKE', "%{$inputPrefix}%")
                ->get(['id','name', 'image', 'price', 'quantity']);

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

            // Sort by similarity
            usort($matches, fn($a, $b) => $b['percent'] <=> $a['percent']);

            // Add top results
            $matches = array_slice($matches, 0, $maxResults);
            foreach ($matches as $m) {
                $products->push($m['product']);
            }
        }


        $products = $products->unique('name');


        $productsArray = $products->map(function ($product) {
            $name = $this->sanitizeUtf8($product->name);
            $image = $product->image ? Storage::url($product->image) : null;

            return [
                'id' => $product->id,
                'name' => $name,
                'image' => $image,
                'price' => $product->price,
                'quantity' => $product->quantity,
            ];
        })->toArray();

        return response()->json([
            'status' => true,
            'data' => $productsArray,
            'message' => empty($productsArray) ? 'No matching products found' : null
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

    // البحث باستخدام LIKE واختيار الأعمدة المطلوبة
    $product = Product::where('name', 'LIKE', "%{$request->name}%")
                      ->select('id', 'name', 'image', 'price', 'quantity', 'active_ingredient')
                      ->first();

    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found'
        ], 404);
    }

    // تحويل الصورة لرابط كامل
    $product->image = $product->main_image_url;

    // جلب كل المنتجات بنفس active_ingredient مع الأعمدة المطلوبة
    $relatedProducts = Product::where('active_ingredient', $product->active_ingredient)
                              ->select('id', 'name', 'image', 'price', 'quantity', 'active_ingredient')
                              ->get();

    // تحويل كل صورة لرابط كامل
    $relatedProducts->transform(function ($item) {
        $item->image = $item->main_image_url;
        return $item;
    });

    return response()->json([
        'status' => true,
        'message' => 'Product fetched successfully',
        'product' => $product,
        'related_products' => $relatedProducts
    ]);
}


}
