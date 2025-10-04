<?php

namespace App\Services;

use App\Models\HealthStatistic;

class HomepageService
{
    public function getHealthStatistics()
    {
        return HealthStatistic::all();
    }
}
