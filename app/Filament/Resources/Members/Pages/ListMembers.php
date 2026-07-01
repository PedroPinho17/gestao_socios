<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Models\ClubSetting;
use App\Support\MemberCardLayout;
use App\Support\ModuleRegistry;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        $reportGroups = [];

        if (ModuleRegistry::enabled(ModuleRegistry::CARTOES)) {
            $reportGroups[] = ActionGroup::make([
                Action::make('cartoes_zip')
                    ->label('Cartões ativos (ZIP)')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->url(route('reports.cards.zip'))
                    ->openUrlInNewTab(),
            ])->dropdown(false);
        }

        if (ModuleRegistry::enabled(ModuleRegistry::RELATORIOS)) {
            $reportGroups[] = ActionGroup::make([
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
            ])->dropdown(false);

            $reportGroups[] = ActionGroup::make([
                Action::make('relatorio_pagantes_pdf')
                    ->label('Sócios pagantes (PDF)')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(route('reports.paying.pdf'))
                    ->openUrlInNewTab(),
                Action::make('relatorio_pagantes_excel')
                    ->label('Sócios pagantes (Excel)')
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    ->url(route('reports.paying.excel'))
                    ->openUrlInNewTab(),
            ])->dropdown(false);
        }

        if ($reportGroups !== []) {
            $actions[] = ActionGroup::make($reportGroups)
                ->label('Relatórios')
                ->icon('heroicon-o-document-arrow-down')
                ->button();
        }

        $actions[] = CreateAction::make();

        return $actions;
    }
}
