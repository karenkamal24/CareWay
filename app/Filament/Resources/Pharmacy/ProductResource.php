<?php

namespace App\Filament\Resources\Pharmacy;

use App\Filament\Resources\Pharmacy\ProductResource\Pages;
use App\Filament\Resources\Pharmacy\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ToggleColumn;

use Filament\Forms\Components\Toggle;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Pharmacy Management';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name') 
                 
                    ->required(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('image')
                    ->image()
                    ->directory('products') 
                  
                    ->imagePreviewHeight(height: '150') 
                    ->columnSpanFull(),
                

                Textarea::make('description')
                    ->maxLength(500),

                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->minValue(0),

                        
                    Toggle::make('status') // ✅ زر التبديل الصحيح داخل الفورم
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),

                ImageColumn::make('image')
                ->getStateUsing(fn ($record) => asset('storage/' . $record->image)) 
                ->size(50)
                ->circular(),

                TextColumn::make('name')->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('price')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->sortable(),

                    ToggleColumn::make('status') 
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Filter by Category'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
