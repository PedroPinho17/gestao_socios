<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\MemberCardBatchExporter;
use App\Services\MemberCardRenderer;
use App\Services\MemberCardViewData;
use App\Support\MemberCardLayout;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberCardController extends Controller
{
    public function show(Member $member, MemberCardViewData $viewData): View
    {
        return view('member-card', $viewData->for($member));
    }

    public function showVerso(Member $member, MemberCardViewData $viewData): View
    {
        $data = $viewData->for($member);

        if (! MemberCardLayout::hasVerso($data['layout'])) {
            abort(404, 'Este cartão não tem verso configurado (texto ou QR).');
        }

        return view('member-card-verso', $data);
    }

    public function pdf(Member $member, MemberCardRenderer $renderer): Response
    {
        return $renderer->pdfResponse($member);
    }

    public function png(Member $member, MemberCardRenderer $renderer): BaseResponse
    {
        return $renderer->pngResponse($member);
    }

    public function pngVerso(Member $member, MemberCardRenderer $renderer): BaseResponse
    {
        return $renderer->pngVersoResponse($member);
    }

    public function exportZip(MemberCardBatchExporter $exporter): StreamedResponse
    {
        return $exporter->zipActiveMembersResponse();
    }
}
