<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cartao')
                ->label('Ver cartão')
                ->icon('heroicon-o-identification')
                ->url(fn (): string => route('member.card', $this->record))
                ->openUrlInNewTab(),
            Action::make('cartao_pdf')
                ->label('Descarregar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn (): string => route('member.card.pdf', $this->record)),
            DeleteAction::make(),
        ];
    }
}
