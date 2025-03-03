<?php

namespace App\Filament\Resources\Pharmacy;

use App\Filament\Resources\Pharmacy\DeliverySettingResource\Pages;
use App\Filament\Resources\Pharmacy\DeliverySettingResource\RelationManagers;
use App\Models\DeliverySetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Columns\TextColumn;


class DeliverySettingResource extends Resource
{
    protected static ?string $model = DeliverySetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pharmacy Management';
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('cost_per_10_meters')
                    ->label('Cost  Meters')
                    ->numeric()
                    ->required(),
                TextInput::make('minimum_fee')
                    ->label('Minimum Delivery Fee')
                    ->numeric()
                    ->required(),
            ]);
    }public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('cost_per_10_meters')
                    ->label('Cost per 10 Meters'),
                TextColumn::make('minimum_fee')
                    ->label('Minimum Fee'),
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
            'index' => Pages\ListDeliverySettings::route('/'),
            'create' => Pages\CreateDeliverySetting::route('/create'),
            'edit' => Pages\EditDeliverySetting::route('/{record}/edit'),
        ];
    }
}
