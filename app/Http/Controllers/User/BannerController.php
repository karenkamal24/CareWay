<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends Controller
{
public function index()
{
    $banners = Banner::all(['id', 'image']);

    return response()->json([
        'status' => true,
        'data' => $banners->map(fn ($banner) => [
            'id' => $banner->id,
            'image_url' => asset('storage/' . $banner->image),
        ]),
    ]);
}

}
