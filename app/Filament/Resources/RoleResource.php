<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Tables;
use App\Models\Role;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Spatie\Permission\Models\Permission;
use Filament\Tables\Actions\DeleteAction;
use App\Models\User;

class RoleResource extends Resource
{
    protected static ?string $model = \Spatie\Permission\Models\Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Roles & Permissions';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Role Name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpan(2),

                Forms\Components\Select::make('users')
                    ->label('Assign Users')
                    ->multiple()
                    ->options(User::pluck('email', 'id'))
                    ->preload()
                    ->relationship('users', 'email')
                    ->required()
                    ->columnSpan(2),

                CheckboxList::make('permissions')
                    ->label('Permissions')
                    ->options(function () {
                        $groupedPermissions = Permission::all()->groupBy('group');
                        $options = [];
                        foreach ($groupedPermissions as $group => $permissions) {
                            foreach ($permissions as $permission) {
                                $options[$permission->id] = "{$group} - {$permission->name}";
                            }
                        }
                        return $options;
                    })
                    ->columns(2)
                    ->required()
                    ->helperText('Select the permissions for this role.')
                    ->saveRelationshipsUsing(function ($record, $state) {
                        $record->permissions()->sync($state);
                    })
                    ->columnSpan(2),
            ])
            ->columns(2);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Role Name'),
                TextColumn::make('users.email')
                    ->label('Users')
                    ->badge()
                    ->limit(3),
                TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->limit(3),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->before(function ($record) {
                        $record->users()->detach();
                        $record->permissions()->detach();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}