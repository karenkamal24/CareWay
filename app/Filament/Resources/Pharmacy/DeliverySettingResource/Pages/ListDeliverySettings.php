<?php

namespace App\Filament\Resources\Pharmacy\DeliverySettingResource\Pages;

use App\Filament\Resources\Pharmacy\DeliverySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliverySettings extends ListRecords
{
    protected static string $resource = DeliverySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
