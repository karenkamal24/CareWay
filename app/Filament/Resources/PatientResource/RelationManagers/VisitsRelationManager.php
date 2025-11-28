<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';
    protected static ?string $title = 'Visits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('symptoms')->label('Symptoms')->required(),
                Forms\Components\Textarea::make('diagnosis')->label('Diagnosis'),
                Forms\Components\Textarea::make('notes')->label('Notes'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('symptoms')->label('Symptoms'),
                Tables\Columns\TextColumn::make('diagnosis')->label('Diagnosis'),
                Tables\Columns\TextColumn::make('created_at')->date()->label('Visit Date'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Visit'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
