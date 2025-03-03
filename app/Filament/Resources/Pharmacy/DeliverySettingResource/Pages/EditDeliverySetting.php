<?php

namespace App\Filament\Resources\Pharmacy\DeliverySettingResource\Pages;

use App\Filament\Resources\Pharmacy\DeliverySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliverySetting extends EditRecord
{
    protected static string $resource = DeliverySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
