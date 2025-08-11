<?php

namespace App\Services;

use App\Models\DoctorReview;
use Illuminate\Support\Facades\Auth;

class DoctorReviewService
{
    public static function createReview(int $doctorId, int $rate, ?string $comment = null): DoctorReview
    {
        // نفترض المستخدم مسجل دخول
        $userId = Auth::id();

        return DoctorReview::create([
            'doctor_id' => $doctorId,
            'user_id' => $userId,
            'rate' => $rate,
            'comment' => $comment,
        ]);
    }

    public static function getReviewsForDoctor(int $doctorId)
    {
        return DoctorReview::where('doctor_id', $doctorId)
            ->with('user:id,name') // إظهار اسم المستخدم فقط
            ->latest()
            ->get();
    }
}
