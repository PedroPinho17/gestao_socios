<?php

namespace App\Modules\Members\Filament\Resources\Members\Actions;

use App\Modules\Members\Services\MemberImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ImportMembersAction
{
    public static function make(): Action
    {
        return Action::make('importarExcel')
            ->label('Importar Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalHeading('Importar sócios por Excel')
            ->modalDescription('Carregue um ficheiro .xlsx, .xls ou .csv. A primeira linha de cada sócio deve ter todos os dados; linhas seguintes com o mesmo número podem trazer apenas pagamentos (deixe o nome e os restantes campos vazios).')
            ->modalSubmitActionLabel('Importar')
            ->schema([
                FileUpload::make('ficheiro')
                    ->label('Ficheiro Excel')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                    ])
                    ->disk('local')
                    ->directory('imports/members')
                    ->maxSize(10240)
                    ->helperText('Formatos: .xlsx, .xls ou .csv (máx. 10 MB).'),
                Toggle::make('atualizar_existentes')
                    ->label('Actualizar sócios existentes (por número)')
                    ->default(true)
                    ->helperText('Se desactivado, linhas com número já registado são ignoradas.'),
            ])
            ->action(function (array $data, MemberImportService $importService): void {
                $relativePath = $data['ficheiro'] ?? null;

                if (blank($relativePath)) {
                    Notification::make()
                        ->title('Ficheiro em falta')
                        ->body('Seleccione um ficheiro Excel para importar.')
                        ->danger()
                        ->send();

                    return;
                }

                $absolutePath = Storage::disk('local')->path($relativePath);
                $result = $importService->import(
                    $absolutePath,
                    (bool) ($data['atualizar_existentes'] ?? true),
                );

                Storage::disk('local')->delete($relativePath);

                if ($result->processed() === 0 && $result->hasErrors()) {
                    Notification::make()
                        ->title('Importação falhou')
                        ->body(self::formatErrors($result->errors))
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                $notification = Notification::make()
                    ->title('Importação concluída')
                    ->body($result->summary());

                if ($result->hasErrors()) {
                    $notification
                        ->warning()
                        ->body($result->summary()."\n\n".self::formatErrors($result->errors))
                        ->persistent();
                } else {
                    $notification->success();
                }

                $notification->send();
            });
    }

    public static function exportAction(): Action
    {
        return Action::make('exportarExcel')
            ->label('Exportar Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->url(route('members.export.excel'))
            ->openUrlInNewTab();
    }

    public static function templateAction(): Action
    {
        return Action::make('modeloImportacao')
            ->label('Modelo Excel')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->url(route('members.import.template'))
            ->openUrlInNewTab();
    }

    /**
     * @param  list<array{row: int, message: string}>  $errors
     */
    protected static function formatErrors(array $errors): string
    {
        $lines = array_map(
            fn (array $error): string => "Linha {$error['row']}: {$error['message']}",
            array_slice($errors, 0, 8),
        );

        if (count($errors) > 8) {
            $lines[] = '… e mais '.(count($errors) - 8).' erro(s).';
        }

        return implode("\n", $lines);
    }
}
