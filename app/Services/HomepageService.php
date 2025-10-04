<?php

namespace App\Services;

use App\Models\HealthStatistic;
use App\Models\MedicalArticle;
use App\Models\GalleryImage;

class HomepageService
{
    public function getHealthStatistics()
    {
        return HealthStatistic::all();
    }
    public function getAll()
    {
        return MedicalArticle::all();
    }

        public function getAllGalleryImage()
    {
        return GalleryImage::all()->map(function ($image) {
            return [
                'id'  => $image->id,
                'url' => url("storage/{$image->url}"),
            ];
        });
    }

}
