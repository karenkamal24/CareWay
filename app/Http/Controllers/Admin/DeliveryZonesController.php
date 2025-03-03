<?php

namespace App\Http\Controllers\Admin;
use App\Models\DeliveryZone;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeliveryZonesController extends Controller
{
    public function getZones()
    {
        return response()->json(DeliveryZone::all());
    }
}
