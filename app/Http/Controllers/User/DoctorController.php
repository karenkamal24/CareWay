<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;

use App\Models\AvailableDoctor;

class DoctorController extends Controller
{
public function index()
{
    try {
        $doctors = Doctor::all();
        $doctorsData = $doctors->map(function ($doctor) {
            $doctor->image_url = url('storage/' . $doctor->image);
            $doctor->average_rate = $doctor->averageRate();
            return $doctor;
        });
        return response()->json($doctorsData);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while fetching doctors data'], 500);
    }
}

public function show($id)
{
    try {
        $doctor = Doctor::with('availableAppointments', 'reviews')->find($id);
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }
        $doctor->image_url = url('storage/' . $doctor->image);
        $doctor->average_rate = $doctor->averageRate(); 
        return response()->json($doctor);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while fetching doctor data'], 500);
    }
}





}
