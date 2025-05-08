<?php

namespace App\Filament\Resources;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Doctor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Filters\SelectFilter;


class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationGroup = 'Hospital Management';

 
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && self::isDoctorUser($user)) {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                return $query->where('doctor_id', $doctor->id);
            }
        }

        return $query;
    }


    protected static function isDoctorUser($user): bool
    {
        if ($user->usertype === 'doctor') {
            return true;
        }

     
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('doctor');
        }

      
        return Doctor::where('user_id', $user->id)->exists();
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isDoctor = $user && self::isDoctorUser($user);

        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->disabled(),

                Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->required()
                    ->disabled(),

                
                
                    Placeholder::make('slot_details')
                    ->label('Slot Details')
                    ->content(function ($get) {
                        $slot = \App\Models\AvailableDoctor::find($get('available_doctor_id'));
                        if ($slot) {
                            return "{$slot->id}: {$slot->day} - {$slot->start_time} to {$slot->end_time} ({$slot->type})";
                        }
                        return 'Select a slot';
                    }),
                
                

                DateTimePicker::make('appointment_time')
                    ->required()
                    ->disabled(),

                Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                   
                    ->disabled(),

                TextInput::make('amount')
                    ->numeric()
                    ->disabled(),


                TextInput::make('paymob_order_id')
                    ->nullable()
                    ->disabled(),

                Select::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Patient'),
                TextColumn::make('doctor.name')->label('Doctor'),
                TextColumn::make('appointment_time')->dateTime(),
                TextColumn::make('slot_details')
                ->label('Slot Details')
                ->getStateUsing(function ($record) {
                    $availableDoctor = \App\Models\AvailableDoctor::find($record->available_doctor_id);
                    return $availableDoctor
                        ? "{$record->available_doctor_id}: {$availableDoctor->day} - {$availableDoctor->start_time} to {$availableDoctor->end_time} ({$availableDoctor->type})"
                        : 'Select a slot';
                })
                ->badge()
                ->sortable(),
                TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ]),
                   
         
            ])
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [];
    }
 

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}