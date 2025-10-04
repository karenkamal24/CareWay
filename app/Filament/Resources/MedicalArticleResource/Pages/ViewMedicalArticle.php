<?php

namespace App\Filament\Resources\MedicalArticleResource\Pages;

use App\Filament\Resources\MedicalArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMedicalArticle extends ViewRecord
{
    protected static string $resource = MedicalArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
