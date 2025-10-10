<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Get all services
     */
    public function index()
    {
        // Just fetch all services — image_url comes automatically from the accessor
        $services = Service::all();

        return response()->json($services);
    }

    /**
     * Get a single service by ID
     */
    public function show(Service $service)
    {
        // The model accessor automatically adds image_url
        return response()->json($service);
    }
}
