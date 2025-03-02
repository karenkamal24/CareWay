<?php

namespace App\Filament\Resources;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use App\Models\AvailableDoctor;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Hospital Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('name')
                    ->required(),
    
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
    
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
    
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department')
                    ->required(),
    
                TextInput::make('specialization')
                    ->label('Specialization')
                    ->required(),
    
                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->required(),
    
                Textarea::make('description')
                    ->label('Description')
                    ->nullable(),
    
                TextInput::make('price')
                    ->label('Consultation Fee')
                    ->numeric()
                    ->required(),
    
                    FileUpload::make('image')
                    ->image()  
                    ->directory('doctor') 
                    ->imagePreviewHeight(150) 
                    ->columnSpanFull(), 
                
    
                Toggle::make('status')
                    ->label('Active')
                    ->default(true),
    
                Repeater::make('availableAppointments')
                    ->relationship()
                    ->label('Available Appointments')
                    ->schema([
                        TextInput::make('start_time')
                            ->label('Start Time')
                            ->type('time')
                            ->required(),
    
                        TextInput::make('end_time')
                            ->label('End Time')
                            ->type('time')
                            ->required(),
    
                        Select::make('day')
                            ->label('Day')
                            ->options([
                                'Sunday' => 'Sunday',
                                'Monday' => 'Monday',
                                'Tuesday' => 'Tuesday',
                                'Wednesday' => 'Wednesday',
                                'Thursday' => 'Thursday',
                                'Friday' => 'Friday',
                                'Saturday' => 'Saturday',
                            ])
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }
    public static function table(Tables\Table $table): Tables\Table
{
    return $table
        ->columns([
            ImageColumn::make('image')
                ->getStateUsing(fn ($record) => asset('storage/' . $record->image))
                ->size(50)
                ->circular(),

            TextColumn::make('name')
                ->label('Name')
                ->searchable(),

            TextColumn::make('email')
                ->label('Email')
                ->searchable(),

            TextColumn::make('department.name')
                ->label('Department')
                ->sortable(),

            TextColumn::make('specialization')
                ->label('Specialization'),

            TextColumn::make('price')
                ->label('Consultation Fee')
                ->money('USD'),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger'),
        ])
        ->filters([
            SelectFilter::make('department')
                ->relationship('department', 'name')
                ->label('Filter by Department'),
        ])
        ->actions([
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->defaultSort('name');
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
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
 
  
}
