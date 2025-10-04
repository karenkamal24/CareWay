<?php

namespace App\Filament\Resources\HealthStatisticResource\Pages;

use App\Filament\Resources\HealthStatisticResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHealthStatistic extends EditRecord
{
    protected static string $resource = HealthStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
