<?php

namespace App\Filament\Resources;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BooleanColumn;

class DepartmentResource extends Resource 
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Hospital Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Department Name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->nullable(),

                    FileUpload::make('image')
                    ->image()
                    ->directory('departments') 
                    ->imagePreviewHeight('150') 
                    ->columnSpanFull(),

                Toggle::make('status')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                ImageColumn::make('image')
                    ->getStateUsing(fn ($record) => asset('storage/' . $record->image))
                    ->size(50)
                    ->circular(),
                TextColumn::make('name')->label('Department Name')->sortable(),
                TextColumn::make('description')->label('Description')->limit(50),
                BooleanColumn::make('status')->label('Active'),
                TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])
     
            ->actions([
                Tables\Actions\EditAction::make(), 
                Tables\Actions\DeleteAction::make(), 
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(), 
            ]);
    }

    public static function getRelations(): array
    {
        return [
        
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
   
}
