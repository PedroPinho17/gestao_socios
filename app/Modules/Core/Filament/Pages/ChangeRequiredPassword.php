<?php

namespace App\Modules\Core\Filament\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * @property-read Schema $form
 */
class ChangeRequiredPassword extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'change-required-password';

    protected static ?string $title = 'Alterar password';

    protected string $view = 'filament.pages.change-required-password';

    public ?array $data = [];

    public function mount(): void
    {
        if (! auth()->user()?->must_change_password) {
            $this->redirect(Filament::getUrl());

            return;
        }

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->label('Nova password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::defaults())
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label('Confirmar nova password')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('Por segurança, deve definir uma nova password antes de continuar.')
                    ->schema([
                        Form::make([EmbeddedSchema::make('form')])
                            ->id('change-password-form')
                            ->livewireSubmitHandler('save')
                            ->footer([
                                Actions::make([
                                    Action::make('save')
                                        ->label('Guardar e continuar')
                                        ->submit('save'),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ])->save();

        Notification::make()
            ->title('Password alterada com sucesso')
            ->success()
            ->send();

        $this->redirect(Filament::getUrl());
    }
}
