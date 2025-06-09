<?php

namespace App\Services\Pharmacy;

use Illuminate\Support\Facades\Config;
use App\Models\DeliverySetting;

class DeliveryService
{
    /**
     * حساب المسافة بين نقطتين باستخدام قانون هافرساين
     *
     * @param float $lat1 إحداثيات خط العرض للنقطة الأولى
     * @param float $lon1 إحداثيات خط الطول للنقطة الأولى
     * @param float $lat2 إحداثيات خط العرض للنقطة الثانية
     * @param float $lon2 إحداثيات خط الطول للنقطة الثانية
     * @return float المسافة بالكيلومترات
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // نصف قطر الأرض بالكيلومترات

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // المسافة بالكيلومترات
    }

    /**
     * حساب تكلفة التوصيل بناءً على المسافة
     *
     * @param float $distanceKm المسافة بالكيلومترات
     * @return float تكلفة التوصيل بالجنيه المصري
     */
    public function calculateDeliveryFee($distanceKm)
    {
        $settings = DeliverySetting::first();

        if (!$settings) {
            throw new \Exception("Delivery settings not found!");
        }

        $costPerKm = $settings->cost_per_km ?? 5.00;
        $minimumFee = $settings->minimum_fee ?? 20.00;
        $maximumFee = 200.00; // الحد الأقصى للتوصيل

        $fee = ceil($distanceKm) * $costPerKm;

        // التأكد من أن الرسوم بين الحد الأدنى والأقصى
        return min(max($fee, $minimumFee), $maximumFee);
    }

    /**
     * حساب المسافة بين الصيدلية والمستخدم
     *
     * @param float $userLat إحداثيات خط العرض للمستخدم
     * @param float $userLon إحداثيات خط الطول للمستخدم
     * @return float المسافة بالكيلومترات
     */
    public function getDistanceFromPharmacy($userLat, $userLon)
    {
        $pharmacyLat = config('pharmacy.pharmacy_latitude');
        $pharmacyLon = config('pharmacy.pharmacy_longitude');

        return $this->calculateDistance($pharmacyLat, $pharmacyLon, $userLat, $userLon);
    }

    /**
     * حساب تكلفة التوصيل بناءً على موقع المستخدم
     *
     * @param float $userLat إحداثيات خط العرض للمستخدم
     * @param float $userLon إحداثيات خط الطول للمستخدم
     * @return float تكلفة التوصيل بالجنيه المصري
     */
    public function getDeliveryFeeFromPharmacy($userLat, $userLon)
    {
        $distanceKm = $this->getDistanceFromPharmacy($userLat, $userLon);
        return $this->calculateDeliveryFee($distanceKm);
    }
}
