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

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

 
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
                    ->content(fn ($get) => optional(\App\Models\AvailableDoctor::find($get('available_doctor_id')))
                        ? "{$get('available_doctor_id')}: " . \App\Models\AvailableDoctor::find($get('available_doctor_id'))->day
                            . ' - ' . \App\Models\AvailableDoctor::find($get('available_doctor_id'))->start_time
                            . ' to ' . \App\Models\AvailableDoctor::find($get('available_doctor_id'))->end_time
                            . ' (' . \App\Models\AvailableDoctor::find($get('available_doctor_id'))->type . ')'
                        : 'Select a slot'),
                
                

                DateTimePicker::make('appointment_time')
                    ->required(),

                Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->numeric()
                    ->required(),


                TextInput::make('paymob_order_id')
                    ->nullable(),

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
                TextColumn::make('payment_status')->badge(),
                TextColumn::make('status')->badge(),
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