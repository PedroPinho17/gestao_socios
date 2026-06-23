<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\MemberCardViewData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MemberCardController extends Controller
{
    public function show(Member $member, MemberCardViewData $viewData): View
    {
        return view('member-card', $viewData->for($member));
    }

    public function pdf(Member $member, MemberCardViewData $viewData): Response
    {
        $data = $viewData->for($member);

        $pdf = Pdf::loadView('member-card-pdf', $data)
            ->setPaper([0, 0, 242.65, 153.07], 'landscape');

        $safeName = preg_replace('/[^\w\-]+/u', '_', $member->nome) ?: 'socio';

        return $pdf->download("cartao_{$member->numero}_{$safeName}.pdf");
    }
}
