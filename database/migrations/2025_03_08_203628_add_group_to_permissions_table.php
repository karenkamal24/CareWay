<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteAction;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
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

                Select::make('users')
                    ->label('Assign Users')
                    ->multiple()
                    ->options(User::pluck('email', 'id'))
                    ->required()
                    ->columnSpan(2),

                CheckboxList::make('permissions')
                    ->label('Permissions')
                    ->options(function () {
                        return Permission::all()
                            ->groupBy('group') // تجميع الأذونات حسب المجموعة
                            ->mapWithKeys(function ($permissions, $group) {
                                return [
                                    ucfirst($group) => $permissions->pluck('name', 'id')->toArray()
                                ];
                            })
                            ->toArray();
                    })
                    ->columns(2)
                    ->helperText('حدد الأذونات المناسبة لهذا الدور.')
                    ->saveRelationshipsUsing(fn($record, $state) => $record->permissions()->sync($state))
                    ->columnSpan(2), // ✅ تم إصلاح الخطأ هنا بإضافة الفاصلة المنقوطة
            ]);
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
                    ->before(fn($record) => $record->users()->detach() && $record->permissions()->detach()),
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
