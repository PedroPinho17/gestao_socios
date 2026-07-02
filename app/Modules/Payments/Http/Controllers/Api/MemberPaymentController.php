<?php

namespace App\Modules\Payments\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Modules\Payments\Services\PaymentReceiptRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MemberPaymentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $member = $this->member($request);

        $payments = $member->payments()
            ->orderByDesc('data')
            ->paginate(15);

        return PaymentResource::collection($payments);
    }

    public function receipt(Request $request, int $payment, PaymentReceiptRenderer $renderer): Response
    {
        $member = $this->member($request);

        $record = $member->payments()->findOrFail($payment);

        return $renderer->download($record);
    }
}
