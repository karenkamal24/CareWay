<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\AvailableDoctor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;

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
            } else {
                return $query->whereRaw('0 = 1'); // فارغة إذا لا يوجد دكتور مرتبط
            }
        }

        return $query;
    }

    protected static function isDoctorUser($user): bool
    {
        if ($user->user_type === 'doctor') {
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('doctor')) {
            return true;
        }

        return Doctor::where('user_id', $user->id)->exists();
    }

    public static function form(Form $form): Form
    {
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
                        $slot = AvailableDoctor::find($get('available_doctor_id'));
                        if ($slot) {
                            $dayName = $slot->day_of_week ?: '-';
                            return "{$slot->id}: {$dayName} - {$slot->start_time} to {$slot->end_time} ({$slot->type})";
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
                Tables\Columns\TextColumn::make('user.name')->label('Patient'),
                Tables\Columns\TextColumn::make('doctor.name')->label('Doctor'),
                Tables\Columns\TextColumn::make('appointment_time')->dateTime(),
                Tables\Columns\TextColumn::make('slot_details')
                    ->label('Slot Details')
                    ->getStateUsing(function ($record) {
                        $slot = AvailableDoctor::find($record->available_doctor_id);
                        if ($slot) {
                            $dayName = $slot->day_of_week ?: '-';
                            return "{$slot->id}: {$dayName} - {$slot->start_time} to {$slot->end_time} ({$slot->type})";
                        }
                        return '-';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ]),

                Filter::make('appointment_date')
                    ->label('Appointment Date')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['from'])) {
                            $query->whereDate('appointment_time', '>=', $data['from']);
                        }
                        if (!empty($data['until'])) {
                            $query->whereDate('appointment_time', '<=', $data['until']);
                        }
                    }),
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
            'chat' => Pages\Chat::route('/{record}/messages'),
        ];
    }
}
