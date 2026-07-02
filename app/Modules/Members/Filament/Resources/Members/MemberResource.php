<?php

namespace App\Modules\Members\Filament\Resources\Members;

use App\Models\Member;
use App\Modules\Core\Filament\Concerns\RequiresModuleFeature;
use App\Modules\Members\Filament\Resources\Members\Pages\CreateMember;
use App\Modules\Members\Filament\Resources\Members\Pages\EditMember;
use App\Modules\Members\Filament\Resources\Members\Pages\ListMembers;
use App\Modules\Members\Filament\Resources\Members\RelationManagers\PaymentsRelationManager;
use App\Modules\Members\Filament\Resources\Members\Schemas\MemberForm;
use App\Modules\Members\Filament\Resources\Members\Tables\MembersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MemberResource extends Resource
{
    use RequiresModuleFeature;

    protected static ?string $model = Member::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Sócios';

    protected static ?string $modelLabel = 'sócio';

    protected static ?string $pluralModelLabel = 'sócios';

    protected static string|UnitEnum|null $navigationGroup = 'Gestão';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nome';

    protected static function moduleFeatureKey(): string
    {
        return 'filament.members';
    }

    protected static function authorizeModuleFeatureAccess(): bool
    {
        return auth()->check();
    }

    public static function form(Schema $schema): Schema
    {
        return MemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}
