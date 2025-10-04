<?php

namespace App\Filament\Resources\MedicalArticleResource\Pages;

use App\Filament\Resources\MedicalArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalArticles extends ListRecords
{
    protected static string $resource = MedicalArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
