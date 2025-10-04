<?php

namespace App\Filament\Resources\MedicalArticleResource\Pages;

use App\Filament\Resources\MedicalArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalArticle extends EditRecord
{
    protected static string $resource = MedicalArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
