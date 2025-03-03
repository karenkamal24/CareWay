<?php

namespace App\Filament\Resources\Pharmacy;

use App\Filament\Resources\Pharmacy\OrderResource\Pages;
use App\Filament\Resources\Pharmacy\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;  
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
class OrderResource extends Resource
{protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Orders cash';
    protected static ?string $navigationGroup = 'Pharmacy Management';


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
           Select::make('status')
                ->label('Order Status')
                ->options([
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'canceled' => 'Canceled',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Order::where('payment_method', 'cash'))
            ->columns([
                TextColumn::make('id')->label('Order ID')->sortable(),
                TextColumn::make('name')->label('Customer Name')->searchable(),
               TextColumn::make('phone')->label('Phone')->searchable(),
                TextColumn::make('total_price')->label('Total Price')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')->label('Order Date')->dateTime(),
               
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'canceled' => 'Canceled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(fn (Form $form) => self::form($form))
                    ->label('Update Status')
                    ->modalHeading('Update Order Status')
                    ->icon('heroicon-o-pencil'),
            ])
            ->bulkActions([]); // Disable bulk actions
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
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
