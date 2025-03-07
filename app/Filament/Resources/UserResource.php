<?php  
namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;


class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'publish'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->unique(User::class, 'email')
                ->maxLength(255),

            Forms\Components\TextInput::make('password')
                ->password()
                ->nullable()
                ->maxLength(255),
                Forms\Components\Select::make('user_type')
                 ->label('User Type')
                  ->options([
                 'admin' => 'admin',
                  'customer' => 'user',
                    ])
                ->default('customer') // تعيين القيمة الافتراضية
                ->required(),


            // اختيار الأدوار
            Forms\Components\Select::make('roles')
                ->label('Roles')
                ->options(Role::query()->pluck('name', 'name')) // استخدام name بدلاً من id
                ->multiple()
                ->preload()
                ->afterStateUpdated(function ($state, $set, $record) {
                    if ($record) {
                        $record->syncRoles($state);
                    }
                }),

            // اختيار الصلاحيات
            Forms\Components\Select::make('permissions')
                ->label('Permissions')
                ->options(Permission::query()->pluck('name', 'name')) // استخدام name بدلاً من id
                ->multiple()
                ->preload()
                ->afterStateUpdated(function ($state, $set, $record) {
                    if ($record) {
                        $record->syncPermissions($state);
                    }
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('email')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('roles.name')->badge()->label('Roles'),
            Tables\Columns\TextColumn::make('permissions.name')->badge()->label('Permissions'),
        ])->filters([])->actions([
            Tables\Actions\EditAction::make(),
        ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
