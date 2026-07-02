<?php

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuotaStatusResource;
use App\Modules\Members\Services\QuotaService;
use Illuminate\Http\Request;

class MemberQuotaController extends Controller
{
    public function show(Request $request, QuotaService $quotaService): QuotaStatusResource
    {
        $member = $this->member($request);
        $member->loadMissing(['quotaPlan.periodicidade', 'payments']);

        $situation = $quotaService->getSituation($member);

        return new QuotaStatusResource([
            'situation' => $situation,
            'member' => $member,
        ]);
    }
}
