<?php

namespace App\Modules\Members\Filament\Resources\Members\Schemas;

use App\Models\Member;
use App\Services\MemberAccountService;
use App\Services\QuotaService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do sócio')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número de sócio')
                            ->required()
                            ->maxLength(50)
                            ->default(fn () => Member::nextNumero()),
                        TextInput::make('nome')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(50),
                        DatePicker::make('data_adesao')
                            ->label('Data de adesão')
                            ->required()
                            ->default(now()),
                        Select::make('quota_plan_id')
                            ->label('Plano de quota')
                            ->relationship('quotaPlan', 'nome')
                            ->searchable()
                            ->preload()
                            ->placeholder('— Nenhum —'),
                        TextInput::make('cargo_cartao')
                            ->label('Texto extra no cartão')
                            ->placeholder('Ex.: cargo, equipa')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        DatePicker::make('validade_manual')
                            ->label('Validade no cartão (opcional)')
                            ->helperText('Se vazio, usa o próximo vencimento calculado pelos pagamentos.'),
                        Toggle::make('ativo')
                            ->label('Sócio ativo')
                            ->default(true),
                        FileUpload::make('foto_path')
                            ->label('Fotografia (cartão)')
                            ->image()
                            ->disk('local')
                            ->directory('members/photos')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Textarea::make('notas')
                            ->label('Notas internas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Situação da quota')
                    ->visible(fn (?Member $record) => filled($record))
                    ->schema([
                        Placeholder::make('quota_situacao')
                            ->label('Estado atual')
                            ->content(function (?Member $record): string {
                                if (! $record) {
                                    return '—';
                                }

                                $record->loadMissing(['quotaPlan', 'payments']);

                                return app(QuotaService::class)->formatSituationLabel(
                                    app(QuotaService::class)->getSituation($record),
                                );
                            }),
                    ]),
                Section::make('Área do sócio')
                    ->description('Acesso ao frontend (login do sócio).')
                    ->visible(fn (?Member $record) => filled($record))
                    ->schema([
                        Placeholder::make('conta_acesso')
                            ->label('Conta de login')
                            ->content(function (?Member $record): string {
                                if (! $record) {
                                    return '—';
                                }

                                $record->loadMissing('user');
                                $account = app(MemberAccountService::class)->accountFor($record);

                                if (! $account) {
                                    return 'Sem conta — use «Criar conta de acesso» no topo da página.';
                                }

                                $status = $account->must_change_password
                                    ? ' (deve alterar a password no primeiro acesso)'
                                    : '';

                                return "Email: {$account->email}{$status}";
                            }),
                    ]),
            ]);
    }
}
