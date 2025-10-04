<?php

namespace App\Filament\Resources\HealthStatisticResource\Pages;

use App\Filament\Resources\HealthStatisticResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHealthStatistic extends ViewRecord
{
    protected static string $resource = HealthStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
