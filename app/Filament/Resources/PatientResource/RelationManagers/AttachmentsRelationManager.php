<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';
    protected static ?string $title = 'Attachments';

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
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('type'),
                Tables\Columns\ImageColumn::make('file_path')->label('File'),
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
}
