<?php

namespace App\Filament\Resources\Pharmacy\ProductResource\Pages;

use App\Filament\Resources\Pharmacy\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
