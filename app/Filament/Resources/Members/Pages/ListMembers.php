<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cartoes_zip')
                ->label('Cartões ativos (ZIP)')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('success')
                ->url(route('reports.cards.zip'))
                ->openUrlInNewTab(),
            Action::make('relatorio_atraso_pdf')
                ->label('Sócios em atraso (PDF)')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->url(route('reports.overdue.pdf'))
                ->openUrlInNewTab(),
            Action::make('relatorio_atraso_excel')
                ->label('Sócios em atraso (Excel)')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(route('reports.overdue.excel'))
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
