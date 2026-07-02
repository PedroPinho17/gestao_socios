<?php

namespace App\Modules\Members\Filament\Resources\Members\Pages;

use App\Models\ClubSetting;
use App\Modules\Members\Filament\Resources\Members\Actions\CreateMemberAccountAction;
use App\Modules\Members\Filament\Resources\Members\MemberResource;
use App\Modules\Members\Support\MemberCardLayout;
use App\Support\FeatureRegistry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (FeatureRegistry::enabled('api.area_socio')) {
            $actions[] = CreateMemberAccountAction::make();
        }

        if (FeatureRegistry::enabled('filament.cards')) {
            $actions[] = Action::make('cartao')
                ->label('Ver cartão')
                ->icon('heroicon-o-identification')
                ->url(fn (): string => route('member.card', $this->record))
                ->openUrlInNewTab();
            $actions[] = Action::make('cartao_pdf')
                ->label('PDF (gráfica)')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn (): string => route('member.card.pdf', $this->record))
                ->openUrlInNewTab();
            $actions[] = Action::make('cartao_png')
                ->label('PNG 300 DPI')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->url(fn (): string => route('member.card.png', $this->record))
                ->openUrlInNewTab();
            $actions[] = Action::make('cartao_verso')
                ->label('Ver verso')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->visible(fn (): bool => MemberCardLayout::hasVerso(
                    MemberCardLayout::resolve(ClubSetting::current()),
                ))
                ->url(fn (): string => route('member.card.verso', $this->record))
                ->openUrlInNewTab();
            $actions[] = Action::make('cartao_png_verso')
                ->label('PNG verso')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->visible(fn (): bool => MemberCardLayout::hasVerso(
                    MemberCardLayout::resolve(ClubSetting::current()),
                ))
                ->url(fn (): string => route('member.card.png.verso', $this->record))
                ->openUrlInNewTab();
        }

        $actions[] = DeleteAction::make();

        return $actions;
    }
}
