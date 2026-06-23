<?php

namespace App\Filament\Pages;

use App\Models\ClubSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ClubSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Definições';

    protected static ?string $title = 'Definições do clube';

    protected static string|UnitEnum|null $navigationGroup = 'Configuração';

    protected static ?int $navigationSort = 8;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageClub() ?? false;
    }

    protected string $view = 'filament.pages.club-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(ClubSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Clube')
                    ->schema([
                        TextInput::make('nome_clube')
                            ->label('Nome do clube / associação')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('logo_path')
                            ->label('Logótipo (cartão)')
                            ->image()
                            ->disk('local')
                            ->directory('club/logos'),
                    ]),
                Section::make('Cartão de sócio')
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('card_gradient_from')
                            ->label('Cor inicial do gradiente'),
                        ColorPicker::make('card_gradient_to')
                            ->label('Cor final do gradiente'),
                        ColorPicker::make('card_accent_color')
                            ->label('Cor de realce')
                            ->columnSpanFull(),
                        TextInput::make('card_titulo')
                            ->label('Rótulo acima do nome')
                            ->default('Sócio'),
                        TextInput::make('card_campo_extra_label')
                            ->label('Legenda do texto extra')
                            ->default('Cargo'),
                        Toggle::make('show_proximo_vencimento')
                            ->label('Mostrar vencimento / validade'),
                        Toggle::make('show_cargo')
                            ->label('Mostrar texto extra (cargo, equipa…)'),
                        Toggle::make('show_email')
                            ->label('Mostrar email'),
                        Toggle::make('show_telefone')
                            ->label('Mostrar telefone'),
                    ]),
            ])
            ->statePath('data')
            ->model(ClubSetting::current());
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('club-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Guardar definições')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        ClubSetting::current()->update($data);

        Notification::make()
            ->title('Definições guardadas')
            ->success()
            ->send();
    }
}
