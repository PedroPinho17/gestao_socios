<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
}
