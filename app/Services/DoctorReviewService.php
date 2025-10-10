<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorReview;
use Illuminate\Support\Facades\Auth;

class DoctorReviewService
{
    public static function createReview(int $doctorId, int $rate, ?string $comment = null): DoctorReview
    {

        $userId = Auth::id();


        $review = DoctorReview::create([
            'doctor_id' => $doctorId,
            'user_id' => $userId,
            'rate' => $rate,
            'comment' => $comment,
        ]);

        $doctor = Doctor::find($doctorId);
        if ($doctor) {
            $average = $doctor->reviews()->avg('rate') ?? 0;


            $doctor->update(['rate' => $average]);
        }

        return $review;
    }

    public static function getReviewsForDoctor(int $doctorId)
    {
        return DoctorReview::where('doctor_id', $doctorId)
            ->with('user:id,name') 
            ->latest()
            ->get();
    }
}
