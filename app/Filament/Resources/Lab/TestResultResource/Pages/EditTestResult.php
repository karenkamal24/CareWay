<?php

namespace App\Filament\Resources\Lab\TestResultResource\Pages;

use App\Filament\Resources\Lab\TestResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTestResult extends EditRecord
{
    protected static string $resource = TestResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
