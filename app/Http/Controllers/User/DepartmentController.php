<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
     // دالة لاستعراض جميع الأقسام
     public function index()
     {
         try {
             $departments = Department::all();


             $departmentsData = $departments->map(function ($department) {

                 if ($department->image) {
                     $department->image_url = url('storage/' . $department->image);
                 } else {
                     $department->image_url = null;
                 }

                 return $department;
             });

             return response()->json($departmentsData);
         } catch (\Exception $e) {

             return response()->json(['error' => 'An error occurred while fetching departments'], 500);
         }
     }


public function show(Request $request, $id)
{
    try {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $response = [
            'category' => $department->category ?? $department->name ?? null,
        ];

        $degreesFilter = $request->query('degrees');

        $doctorsQuery = $department->doctors();

        if ($degreesFilter) {
            $degreesArray = explode(',', $degreesFilter);
            $doctorsQuery->whereIn('degree', $degreesArray);
        }

        $doctors = $doctorsQuery->get(['id', 'name', 'degree', 'specialization', 'price', 'image', 'rate']);

        $doctors->transform(function ($doctor) {
            $doctor->image_url = $doctor->image ? url('storage/' . $doctor->image) : null;
            return $doctor;
        });

        $response['doctors'] = $doctors;

        if ($doctors->isEmpty()) {
            $response['message'] = 'No doctors found for the selected degrees.';
        }

        return response()->json($response);

    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while fetching the department'], 500);
    }
}


public function getDegrees(Request $request, $id)
{
    try {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        // جلب درجات الدكاتره المرتبطين بالقسم بدون تكرار
        $degrees = $department->doctors()
            ->whereNotNull('degree')
            ->distinct()
            ->pluck('degree');

        return response()->json([
            'department_id' => $id,
            'degrees' => $degrees,
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred'], 500);
    }
}




}
