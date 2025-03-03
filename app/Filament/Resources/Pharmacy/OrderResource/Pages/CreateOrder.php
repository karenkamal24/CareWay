<?php

namespace App\Filament\Resources\Pharmacy\OrderResource\Pages;

use App\Filament\Resources\Pharmacy\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
