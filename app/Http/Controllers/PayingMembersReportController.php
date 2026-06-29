<?php

namespace App\Http\Controllers;

use App\Models\ClubSetting;
use App\Services\PayingMembersReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayingMembersReportController extends Controller
{
    public function pdf(PayingMembersReportService $report): Response
    {
        $rows = $report->rows();
        $settings = ClubSetting::current();

        $pdf = Pdf::loadView('reports.paying-members-pdf', [
            'rows' => $rows,
            'settings' => $settings,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = 'socios_pagantes_'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    public function excel(PayingMembersReportService $report): StreamedResponse
    {
        $rows = $report->rows();
        $filename = 'socios_pagantes_'.now()->format('Y-m-d').'.csv';

        return ResponseFactory::streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['N.º', 'Nome', 'Email', 'Telefone', 'Plano', 'Situação', 'Vencimento'], ';');

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['numero'],
                    $row['nome'],
                    $row['email'] ?? '',
                    $row['telefone'] ?? '',
                    $row['plano'] ?? '',
                    $row['situacao'],
                    $row['vencimento'],
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
