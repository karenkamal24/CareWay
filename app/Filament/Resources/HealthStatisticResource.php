<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HealthStatisticResource\Pages;
use App\Filament\Resources\HealthStatisticResource\RelationManagers;
use App\Models\HealthStatistic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HealthStatisticResource extends Resource
{
    protected static ?string $model = HealthStatistic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Statistic Name'),
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->label('Value'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Statistic Name')->sortable(),
                Tables\Columns\TextColumn::make('number')->label('Value')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHealthStatistics::route('/'),
            'create' => Pages\CreateHealthStatistic::route('/create'),
            'view' => Pages\ViewHealthStatistic::route('/{record}'),
            'edit' => Pages\EditHealthStatistic::route('/{record}/edit'),
        ];
    }
}
