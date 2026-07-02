<?php

namespace App\Modules\Members\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Members\Services\MemberExportService;
use App\Modules\Members\Services\MemberImportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberImportController extends Controller
{
    public function template(MemberImportService $importService): StreamedResponse
    {
        return $importService->templateDownloadResponse();
    }

    public function export(MemberExportService $exportService): StreamedResponse
    {
        return $exportService->exportDownloadResponse();
    }
}
