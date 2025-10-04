<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalArticleResource\Pages;
use App\Filament\Resources\MedicalArticleResource\RelationManagers;
use App\Models\MedicalArticle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicalArticleResource extends Resource
{
    protected static ?string $model = MedicalArticle::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
     protected static ?string $navigationGroup = 'Hospital Management';
    protected static ?string $navigationLabel = 'Medical Articles';
    protected static ?string $pluralModelLabel = 'Medical Articles';
    protected static ?string $modelLabel = 'Medical Article';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->url()
                    ->maxLength(500),
            ]);
    }

 public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('title')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('url')
                ->url(fn ($record) => $record->url)
                ->openUrlInNewTab(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime('Y-m-d H:i')
                ->sortable(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
}


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalArticles::route('/'),
            'create' => Pages\CreateMedicalArticle::route('/create'),
            'view' => Pages\ViewMedicalArticle::route('/{record}'),
            'edit' => Pages\EditMedicalArticle::route('/{record}/edit'),
        ];
    }
}
