<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\DoctorReviewService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DoctorReviewController extends Controller
{

    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'rate' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return ApiResponseHelper::error($validator->errors()->first(), 422);
        }

        // Create the review via the service
        $review = DoctorReviewService::createReview(
            $request->doctor_id,
            $request->rate,
            $request->comment
        );

        return ApiResponseHelper::success('Review added successfully', $review);
    }

    // Get all reviews for a specific doctor
    public function index($doctorId)
    {
        $reviews = DoctorReviewService::getReviewsForDoctor($doctorId);

        return ApiResponseHelper::success('List of reviews', $reviews);
    }
}
