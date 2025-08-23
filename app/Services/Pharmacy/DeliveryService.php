<?php

namespace App\Services\Pharmacy;

use Illuminate\Support\Facades\Config;
use App\Models\DeliverySetting;

class DeliveryService
{
    /**
     * حساب المسافة بين نقطتين باستخدام قانون هافرساين
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // كيلومتر
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * حساب تكلفة التوصيل
     */
    public function calculateDeliveryFee($distanceKm)
    {
        $settings = DeliverySetting::first();

        if (!$settings) {
            throw new \Exception("Delivery settings not found!");
        }

        $costPerKm = $settings->cost_per_km ?? 5.0;
        $minimumFee = $settings->minimum_fee ?? 20.0;
        $maximumFee = 200.0;

        $fee = ceil($distanceKm) * $costPerKm;

        return min(max($fee, $minimumFee), $maximumFee);
    }

    /**
     * المسافة بين الصيدلية والمستخدم
     */
    public function getDistanceFromPharmacy($userLat, $userLon)
    {
        $pharmacyLat = config('pharmacy.pharmacy_latitude');
        $pharmacyLon = config('pharmacy.pharmacy_longitude');

        return $this->calculateDistance($pharmacyLat, $pharmacyLon, $userLat, $userLon);
    }

    /**
     * تكلفة التوصيل من الصيدلية
     */
    public function getDeliveryFeeFromPharmacy($userLat, $userLon)
    {
        $distanceKm = $this->getDistanceFromPharmacy($userLat, $userLon);
        return $this->calculateDeliveryFee($distanceKm);
    }

    /**
     * التحقق من إمكانية التوصيل
     */
    public function checkDeliveryAvailability($userLat, $userLon)
    {
        $distanceKm = $this->getDistanceFromPharmacy($userLat, $userLon);

        $settings = DeliverySetting::first();
        if (!$settings) {
            return [
                'available' => false,
                'message' => 'Delivery settings not found!',
                'distance_km' => $distanceKm,
                'delivery_fee' => 0,
                'estimated_time' => null
            ];
        }

        $maxDistance = $settings->max_distance_km ?? 20;
        $deliveryFee = $this->calculateDeliveryFee($distanceKm);

        $available = $distanceKm <= $maxDistance;
        $message = $available ? 'Delivery available' : 'Delivery not available for your location';
        $estimatedTime = ceil($distanceKm * 5); // مثال تقديري بالـ دقائق

        return [
            'available' => $available,
            'message' => $message,
            'distance_km' => $distanceKm,
            'delivery_fee' => $deliveryFee,
            'estimated_time' => $estimatedTime,
        ];
    }
}
