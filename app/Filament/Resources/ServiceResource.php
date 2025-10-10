<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'General Settings';
    protected static ?string $modelLabel = 'Service';
    protected static ?string $pluralModelLabel = 'Services';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Service Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->maxLength(1000),

                Forms\Components\FileUpload::make('image')
                    ->label('Service Image')
                    ->image()
                    ->directory('services')
                    ->imagePreviewHeight('150')
                    ->maxSize(2048),

                Forms\Components\Toggle::make('is_available')
                    ->label('Is Available?')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
Tables\Columns\ImageColumn::make('image_url')
    ->label('Image')
    ->size(150),



                Tables\Columns\TextColumn::make('name')
                    ->label('Service Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->trueLabel('Available')
                    ->falseLabel('Unavailable')
                    ->placeholder('All'),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
