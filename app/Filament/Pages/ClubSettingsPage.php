<?php

namespace App\Filament\Pages;

use App\Models\ClubSetting;
use App\Services\MemberCardViewData;
use App\Support\MemberCardLayout;
use App\Support\MemberCardQrCode;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
        $settings = ClubSetting::current();
        $data = $settings->attributesToArray();
        $data['card_layout'] = $settings->resolvedCardLayout();
        $this->form->fill($data);
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
                            ->maxLength(255)
                            ->live(onBlur: true),
                        ColorPicker::make('panel_primary_color')
                            ->label('Cor principal do painel')
                            ->helperText('Botões, links e realces do backoffice.'),
                        FileUpload::make('logo_path')
                            ->label('Logótipo (cartão)')
                            ->image()
                            ->disk('local')
                            ->directory('club/logos'),
                    ]),
                Section::make('Layout do cartão')
                    ->columns(2)
                    ->schema([
                        Select::make('card_layout.template')
                            ->label('Modelo')
                            ->options(MemberCardLayout::templateOptions())
                            ->required()
                            ->live(),
                        Select::make('card_layout.font_family')
                            ->label('Tipo de letra')
                            ->options(MemberCardLayout::fontOptions())
                            ->live(),
                        Select::make('card_layout.logo_position')
                            ->label('Posição do logótipo')
                            ->options(MemberCardLayout::logoPositionOptions())
                            ->live(),
                        Select::make('card_layout.photo_shape')
                            ->label('Formato da foto')
                            ->options(MemberCardLayout::photoShapeOptions())
                            ->live(),
                        Select::make('card_layout.photo_position')
                            ->label('Posição da foto')
                            ->options([
                                'left' => 'Esquerda',
                                'right' => 'Direita',
                            ])
                            ->live(),
                        TextInput::make('card_layout.numero_prefix')
                            ->label('Prefixo do número')
                            ->placeholder('SOC-')
                            ->maxLength(20)
                            ->live(onBlur: true),
                    ]),
                Section::make('Cores e estilo')
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('card_gradient_from')
                            ->label('Cor inicial do gradiente')
                            ->live(),
                        ColorPicker::make('card_gradient_to')
                            ->label('Cor final do gradiente')
                            ->live(),
                        ColorPicker::make('card_accent_color')
                            ->label('Cor de realce')
                            ->live(),
                        ColorPicker::make('card_layout.text_color')
                            ->label('Cor do texto')
                            ->live(),
                        Toggle::make('card_layout.show_border')
                            ->label('Mostrar borda')
                            ->live(),
                        ColorPicker::make('card_layout.border_color')
                            ->label('Cor da borda')
                            ->live(),
                        TextInput::make('card_layout.border_width')
                            ->label('Espessura da borda (px)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(6)
                            ->default(1),
                    ]),
                Section::make('Textos do cartão')
                    ->columns(2)
                    ->schema([
                        TextInput::make('card_titulo')
                            ->label('Rótulo acima do nome')
                            ->default('Sócio')
                            ->live(onBlur: true),
                        TextInput::make('card_campo_extra_label')
                            ->label('Legenda do cargo / extra')
                            ->default('Cargo')
                            ->live(onBlur: true),
                        TextInput::make('card_layout.card_motto')
                            ->label('Lema do cartão (cabeçalho)')
                            ->placeholder('TRADIÇÃO • ESPORTE • CULTURA')
                            ->maxLength(80)
                            ->live(onBlur: true)
                            ->visible(fn (Get $get): bool => ($get('card_layout.template') ?? 'classic') === 'crc_vale'),
                        TextInput::make('card_layout.card_slogan')
                            ->label('Slogan do cartão (rodapé)')
                            ->placeholder('Juntos Somos Mais Fortes')
                            ->maxLength(80)
                            ->live(onBlur: true)
                            ->visible(fn (Get $get): bool => ($get('card_layout.template') ?? 'classic') === 'crc_vale'),
                        Textarea::make('card_layout.footer_text')
                            ->label('Texto no rodapé')
                            ->placeholder('Válido mediante quota em dia')
                            ->rows(2)
                            ->columnSpanFull()
                            ->live(onBlur: true),
                        Textarea::make('card_layout.verso_text')
                            ->label('Texto no verso')
                            ->rows(3)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->helperText('Texto livre no verso (regulamento, contactos, etc.).'),
                        Toggle::make('card_layout.show_qr_verso')
                            ->label('QR code no verso')
                            ->default(false)
                            ->live()
                            ->helperText('Ao ler o QR, abre uma página pública de validação do sócio (link assinado).'),
                        Select::make('card_layout.qr_content')
                            ->label('Conteúdo do QR')
                            ->options(MemberCardQrCode::contentOptions())
                            ->default('validacao')
                            ->live()
                            ->visible(fn (Get $get): bool => (bool) $get('card_layout.show_qr_verso'))
                            ->helperText('Recomendado: link de validação online.'),
                    ]),
                Section::make('Campos visíveis')
                    ->columns(2)
                    ->schema([
                        Toggle::make('card_layout.show_nome')->label('Nome')->default(true),
                        Toggle::make('card_layout.show_numero')->label('Número de sócio')->default(true),
                        Toggle::make('card_layout.show_foto')->label('Foto')->default(true)->live(),
                        Toggle::make('card_layout.show_validade')->label('Validade / vencimento')->default(true)->live(),
                        Toggle::make('card_layout.show_cargo')->label('Cargo / texto extra')->default(true)->live(),
                        Toggle::make('card_layout.show_plano')->label('Plano de quota')->default(false)->live(),
                        Toggle::make('card_layout.show_email')->label('Email')->default(false)->live(),
                        Toggle::make('card_layout.show_telefone')->label('Telefone')->default(false)->live(),
                        Toggle::make('card_layout.show_adesao')->label('Data de adesão')->default(false)->live(),
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

    /**
     * @return array<string, mixed>
     */
    public function getCardPreviewData(): array
    {
        return app(MemberCardViewData::class)->preview($this->data ?? []);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $cardLayout = $data['card_layout'] ?? [];

        $data['show_proximo_vencimento'] = (bool) ($cardLayout['show_validade'] ?? true);
        $data['show_cargo'] = (bool) ($cardLayout['show_cargo'] ?? true);
        $data['show_email'] = (bool) ($cardLayout['show_email'] ?? false);
        $data['show_telefone'] = (bool) ($cardLayout['show_telefone'] ?? false);
        $data['card_layout'] = $cardLayout;

        ClubSetting::current()->update($data);

        Notification::make()
            ->title('Definições guardadas')
            ->success()
            ->send();
    }
}
