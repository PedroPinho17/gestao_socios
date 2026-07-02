<?php

namespace App\Modules\Notifications\Filament\Pages;

use App\Enums\QuotaSituationKind;
use App\Mail\ClubAnnouncementMail;
use App\Models\Member;
use App\Modules\Core\Filament\Concerns\RequiresModuleFeature;
use App\Services\QuotaService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class CommunicationsPage extends Page
{
    use RequiresModuleFeature;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Comunicações';

    protected static ?string $title = 'Comunicações aos sócios';

    protected static string|UnitEnum|null $navigationGroup = 'Configuração';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.communications';

    public ?array $data = [];

    /**
     * @var array<int, array{nome: string, telefone: string, url: string}>
     */
    public array $whatsappLinks = [];

    protected static function moduleFeatureKey(): string
    {
        return 'filament.communications';
    }

    protected static function authorizeModuleFeatureAccess(): bool
    {
        return auth()->user()?->canManageClub() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'channel' => 'email',
            'audience' => 'ativos',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Destinatários')
                    ->schema([
                        Select::make('channel')
                            ->label('Canal')
                            ->options([
                                'email' => 'Email',
                                'whatsapp' => 'WhatsApp (gera links para enviar)',
                            ])
                            ->required()
                            ->live(),
                        Select::make('audience')
                            ->label('Enviar para')
                            ->options([
                                'ativos' => 'Todos os sócios ativos',
                                'ok' => 'Quota em dia',
                                'due_soon' => 'Quota a vencer em breve',
                                'overdue' => 'Quota em atraso',
                                'custom' => 'Selecionar sócios específicos',
                            ])
                            ->required()
                            ->live(),
                        Select::make('members')
                            ->label('Sócios')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (Get $get): array => Member::query()
                                ->where('ativo', true)
                                ->when($get('channel') === 'whatsapp',
                                    fn ($q) => $q->whereNotNull('telefone'),
                                    fn ($q) => $q->whereNotNull('email'),
                                )
                                ->orderBy('nome')
                                ->get()
                                ->mapWithKeys(fn (Member $m): array => [
                                    $m->id => $m->nome.' ('.($get('channel') === 'whatsapp' ? $m->telefone : $m->email).')',
                                ])
                                ->all())
                            ->visible(fn (Get $get): bool => $get('audience') === 'custom')
                            ->required(fn (Get $get): bool => $get('audience') === 'custom'),
                    ]),
                Section::make('Mensagem')
                    ->schema([
                        TextInput::make('assunto')
                            ->label('Assunto')
                            ->required(fn (Get $get): bool => $get('channel') === 'email')
                            ->visible(fn (Get $get): bool => $get('channel') === 'email')
                            ->maxLength(150),
                        RichEditor::make('corpo')
                            ->label('Mensagem')
                            ->required()
                            ->helperText(fn (Get $get): string => $get('channel') === 'whatsapp'
                                ? 'No WhatsApp a formatação é convertida para texto simples.'
                                : 'A mensagem começa com «Olá {nome do sócio},» automaticamente.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('communications-form')
                    ->livewireSubmitHandler('submitForm')
                    ->footer([
                        Actions::make([
                            Action::make('submitForm')
                                ->label('Enviar / Gerar links')
                                ->icon('heroicon-o-paper-airplane')
                                ->submit('submitForm'),
                        ]),
                    ]),
            ]);
    }

    public function submitForm(): void
    {
        $data = $this->form->getState();

        if (($data['channel'] ?? 'email') === 'whatsapp') {
            $this->generateWhatsappLinks($data);

            return;
        }

        $this->sendEmail($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function sendEmail(array $data): void
    {
        $this->whatsappLinks = [];

        $recipients = $this->resolveRecipients($data, 'email');

        if ($recipients->isEmpty()) {
            Notification::make()
                ->title('Sem destinatários')
                ->body('Nenhum sócio com email corresponde aos critérios selecionados.')
                ->warning()
                ->send();

            return;
        }

        $assunto = (string) ($data['assunto'] ?? '');
        $corpo = (string) ($data['corpo'] ?? '');

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $member) {
            try {
                Mail::to($member->email)->send(new ClubAnnouncementMail($member, $assunto, $corpo));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Falha ao enviar comunicação a sócio', [
                    'member_id' => $member->id,
                    'email' => $member->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'canal' => 'email',
                'assunto' => $assunto,
                'destinatarios' => $sent,
                'falhas' => $failed,
                'audience' => $data['audience'] ?? null,
            ])
            ->log('Comunicação enviada aos sócios');

        if ($failed === 0) {
            Notification::make()
                ->title('Email enviado')
                ->body("Enviado a {$sent} sócio(s).")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Envio concluído com falhas')
                ->body("Enviado a {$sent} sócio(s); {$failed} falha(s). Ver registos.")
                ->warning()
                ->send();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function generateWhatsappLinks(array $data): void
    {
        $recipients = $this->resolveRecipients($data, 'telefone');

        $mensagem = $this->toPlainText((string) ($data['corpo'] ?? ''));

        $links = [];

        foreach ($recipients as $member) {
            $phone = $this->normalizePhone($member->telefone);

            if ($phone === null) {
                continue;
            }

            $texto = 'Olá '.$member->nome.",\n\n".$mensagem;

            $links[] = [
                'nome' => $member->nome,
                'telefone' => (string) $member->telefone,
                'url' => 'https://wa.me/'.$phone.'?text='.rawurlencode($texto),
            ];
        }

        $this->whatsappLinks = $links;

        if ($links === []) {
            Notification::make()
                ->title('Sem destinatários')
                ->body('Nenhum sócio com telemóvel válido corresponde aos critérios selecionados.')
                ->warning()
                ->send();

            return;
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'canal' => 'whatsapp',
                'destinatarios' => count($links),
                'audience' => $data['audience'] ?? null,
            ])
            ->log('Links de WhatsApp gerados para sócios');

        Notification::make()
            ->title('Links gerados')
            ->body(count($links).' link(s) de WhatsApp prontos. Clique em cada um para abrir a conversa.')
            ->success()
            ->send();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, Member>
     */
    protected function resolveRecipients(array $data, string $field): Collection
    {
        $audience = $data['audience'] ?? 'ativos';

        if ($audience === 'custom') {
            return Member::query()
                ->whereIn('id', $data['members'] ?? [])
                ->whereNotNull($field)
                ->get();
        }

        if (in_array($audience, ['ok', 'due_soon', 'overdue'], true)) {
            $kind = match ($audience) {
                'ok' => QuotaSituationKind::Ok,
                'due_soon' => QuotaSituationKind::DueSoon,
                'overdue' => QuotaSituationKind::Overdue,
            };

            return app(QuotaService::class)
                ->membersWithSituation($kind)
                ->filter(fn (Member $m): bool => filled($m->{$field}))
                ->values();
        }

        return Member::query()
            ->where('ativo', true)
            ->whereNotNull($field)
            ->get();
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if ($digits === '') {
            return null;
        }

        // Número nacional (9 dígitos) → assume Portugal (+351).
        if (strlen($digits) === 9) {
            $digits = '351'.$digits;
        }

        return $digits;
    }

    private function toPlainText(string $html): string
    {
        $text = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $text = preg_replace('/<\/(p|div|li)>/i', "\n", $text) ?? $text;
        $text = strip_tags($text);

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5));
    }
}
