<?php

namespace App\Http\Controllers;

use App\Enums\QuotaSituationKind;
use App\Models\Member;
use App\Services\QuotaService;
use App\Support\ClubBranding;
use Illuminate\View\View;

class MemberValidationController extends Controller
{
    public function show(Member $member, QuotaService $quotaService): View
    {
        $member->loadMissing(['quotaPlan.periodicidade']);
        $situation = $quotaService->getSituation($member);

        return view('member-validate', [
            'member' => $member,
            'clubName' => ClubBranding::clubName(),
            'logoUrl' => ClubBranding::logoUrl(),
            'primaryColor' => ClubBranding::primaryColor(),
            'gradientFrom' => ClubBranding::gradientFrom(),
            'gradientTo' => ClubBranding::gradientTo(),
            'accentColor' => ClubBranding::accentColor(),
            'situation' => $situation,
            'statusLabel' => $this->statusLabel($member, $situation),
            'statusTone' => $this->statusTone($situation['kind']),
        ]);
    }

    /**
     * @param  array{kind: QuotaSituationKind, next_due: ?\Carbon\Carbon, days_overdue: ?int, days_until: ?int}  $situation
     */
    private function statusLabel(Member $member, array $situation): string
    {
        if (! $member->ativo) {
            return 'Sócio inativo';
        }

        return match ($situation['kind']) {
            QuotaSituationKind::SemPlano => 'Sem plano de quota',
            QuotaSituationKind::Ok => 'Quota em dia',
            QuotaSituationKind::DueSoon => 'Quota a vencer em breve',
            QuotaSituationKind::Overdue => 'Quota em atraso',
            QuotaSituationKind::Inativo => 'Sócio inativo',
        };
    }

    private function statusTone(QuotaSituationKind $kind): string
    {
        return match ($kind) {
            QuotaSituationKind::Ok => 'ok',
            QuotaSituationKind::DueSoon => 'warn',
            QuotaSituationKind::Overdue => 'bad',
            default => 'neutral',
        };
    }
}
