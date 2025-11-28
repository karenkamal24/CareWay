<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;




class PatientAttachmentsRelationManager extends RelationManager
{
    
    protected static string $relationship = 'user';
    protected static ?string $title = 'Patient Attachments';
    protected static ?string $recordTitleAttribute = 'name';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $patient = $this->getOwnerRecord()->user;
        return \App\Models\PatientAttachment::query()->where('patient_id', $patient->id);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'prescription' => 'Prescription',
                        'lab' => 'Lab Result',
                        'radiology' => 'Radiology',
                        'scan' => 'Scan',
                        'other' => 'Other',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->directory('attachments')
                    ->preserveFilenames()
                    ->required(),

                Forms\Components\Textarea::make('description'),
            ]);
    }

    public function table(Table $table): Table
    {
        $patient = $this->getOwnerRecord()->user;

        return $table
            ->query(\App\Models\PatientAttachment::query()->where('patient_id', $patient->id))
            ->columns([
                Tables\Columns\BadgeColumn::make('type'),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('created_at')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Attachment'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['patient_id'] = $this->getOwnerRecord()->user_id;
        $data['source'] = 'doctor';
        return $data;
    }
    public static function getResourcePermission(): ?string
{
    return 'appointment';
}


public static function canViewForRecord($record, $user): bool
{
    return true;
}


}

