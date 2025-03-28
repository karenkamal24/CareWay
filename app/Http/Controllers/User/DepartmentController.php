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
 
  
     public function show($id)
     {
         try {
             $department = Department::find($id); 
 
             if (!$department) {
                 return response()->json(['error' => 'Department not found'], 404); 
             }
 
             // إضافة رابط الصورة للقسم
             if ($department->image) {
                 $department->image_url = url('storage/' . $department->image);
             } else {
                 $department->image_url = null; 
             }
 
  
             return response()->json($department);
         } catch (\Exception $e) {
         
             return response()->json(['error' => 'An error occurred while fetching the department'], 500);
         }
     }
}
