<?php

namespace App\Filament\Resources\HealthStatisticResource\Pages;

use App\Filament\Resources\HealthStatisticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHealthStatistics extends ListRecords
{
    protected static string $resource = HealthStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
