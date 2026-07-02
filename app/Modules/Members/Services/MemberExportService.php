<?php

namespace App\Modules\Members\Services;

use App\Modules\Members\Models\Member;
use App\Modules\Members\Support\MemberImportColumnMap;
use App\Modules\Members\Support\MemberSpreadsheetWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberExportService
{
    public function exportDownloadResponse(): StreamedResponse
    {
        $rows = [MemberImportColumnMap::templateHeaders()];

        Member::query()
            ->with(['quotaPlan', 'payments' => fn ($query) => $query->reorder()->orderBy('data')->orderBy('id')])
            ->orderByRaw('CAST(numero AS UNSIGNED), numero')
            ->lazy(100)
            ->each(function (Member $member) use (&$rows): void {
                $payments = $member->payments;

                if ($payments->isEmpty()) {
                    $rows[] = MemberImportColumnMap::rowFromMember($member);

                    return;
                }

                foreach ($payments as $index => $payment) {
                    $rows[] = MemberImportColumnMap::rowFromMember(
                        $member,
                        $payment,
                        includeMemberData: $index === 0,
                    );
                }
            });

        $filename = 'socios_'.now()->format('Y-m-d').'.xlsx';

        return MemberSpreadsheetWriter::downloadResponse($rows, $filename);
    }
}
