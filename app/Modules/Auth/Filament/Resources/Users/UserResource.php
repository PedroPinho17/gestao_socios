<?php

namespace App\Modules\Auth\Filament\Resources\Users;

use App\Models\Permissao;
use App\Models\User;
use App\Modules\Auth\Filament\Resources\Users\Pages\CreateUser;
use App\Modules\Auth\Filament\Resources\Users\Pages\EditUser;
use App\Modules\Auth\Filament\Resources\Users\Pages\ListUsers;
use App\Modules\Core\Filament\Concerns\RequiresModuleFeature;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class UserResource extends Resource
{
    use RequiresModuleFeature;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Utilizadores';

    protected static ?string $modelLabel = 'utilizador';

    protected static ?string $pluralModelLabel = 'utilizadores';

    protected static string|UnitEnum|null $navigationGroup = 'Configuração';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    protected static function moduleFeatureKey(): string
    {
        return 'filament.users';
    }

    protected static function authorizeModuleFeatureAccess(): bool
    {
        return auth()->user()?->canManageUsers() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageUsers() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        if (! $record instanceof User) {
            return false;
        }

        return auth()->user()?->canManageUser($record) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        if (! $record instanceof User) {
            return false;
        }

        $user = auth()->user();

        if (! $user?->canManageUser($record)) {
            return false;
        }

        return $record->getKey() !== $user->id;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('permissao_id')
                    ->label('Perfil')
                    ->options(fn (): array => Permissao::query()
                        ->whereIn('id', auth()->user()?->assignablePermissaoIds() ?? [])
                        ->orderBy('id')
                        ->pluck('permissao', 'id')
                        ->all())
                    ->required()
                    ->default(Permissao::TESOUREIRO)
                    ->disabled(fn (?User $record): bool => $record !== null
                        && ! auth()->user()?->canManageUser($record)),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->rule(Password::defaults())
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->confirmed()
                    ->helperText('Mínimo 12 caracteres, com maiúsculas, minúsculas, números e símbolos.'),
                TextInput::make('password_confirmation')
                    ->label('Confirmar password')
                    ->password()
                    ->revealable()
                    ->required(fn (Get $get, string $operation): bool => $operation === 'create' || filled($get('password')))
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $query->with('permissao');

                $user = auth()->user();

                if ($user?->isAdmin() && ! $user->isImperador()) {
                    $query->whereIn('permissao_id', [
                        Permissao::ADMINISTRADOR,
                        Permissao::TESOUREIRO,
                    ]);
                }
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissao.permissao')
                    ->label('Perfil')
                    ->badge()
                    ->color(fn (User $record): string => match ($record->permissao_id) {
                        Permissao::IMPERADOR => 'danger',
                        Permissao::ADMINISTRADOR => 'success',
                        default => 'info',
                    }),
                IconColumn::make('must_change_password')
                    ->label('Mudar password')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => static::canDelete($record)),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
