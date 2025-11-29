<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use App\Models\PatientDisease;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class DiseasesRelationManager extends RelationManager
{
    protected static string $relationship = 'diseases';

    protected static ?string $title = 'Diseases';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('disease_name')
                    ->required()
                    ->label('Disease Name'),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'chronic' => 'Chronic',
                        'resolved' => 'Resolved',
                    ])
                    ->required()
                    ->label('Status'),

                Forms\Components\Select::make('source')
                    ->options([
                        'doctor' => 'Doctor',
                        'patient' => 'Patient',
                        'external' => 'External',
                    ])
                    ->default('doctor')
                    ->label('Source'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('disease_name')
                    ->searchable()
                    ->label('Disease'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'chronic',
                        'danger'  => 'resolved',
                    ])
                    ->label('Status'),

                Tables\Columns\BadgeColumn::make('source')
                    ->colors([
                        'primary' => 'doctor',
                        'warning' => 'patient',
                        'danger' => 'external',
                    ])
                    ->label('Source'),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->label('Added At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'doctor' => 'Doctor',
                        'patient' => 'Patient',
                        'external' => 'External',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'chronic' => 'Chronic',
                        'resolved' => 'Resolved',
                    ]),
            ])

            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Disease'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // Resolve disease
                Tables\Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => $record->status !== 'resolved')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'resolved'
                        ]);
                    })
                    ->successNotificationTitle('Disease marked as resolved'),

                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
