<?php

namespace App\Modules\Auth\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use LaravelWebauthn\Contracts\LoginSuccessResponse as LoginSuccessResponseContract;
use LaravelWebauthn\Http\Responses\LoginSuccessResponse;

class StaffWebauthnLoginSuccessResponse extends LoginSuccessResponse implements LoginSuccessResponseContract
{
    protected function jsonResponse(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return Response::json([
            'result' => true,
            'callback' => Filament::getPanel('admin')->getUrl(),
        ]);
    }
}
