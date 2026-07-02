<?php

namespace App\Http\Controllers;

use App\Modules\Members\Models\Member;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function member(Request $request): Member
    {
        $user = $request->user();

        if (! $user?->isMember() || ! $user->member) {
            abort(403, 'Acesso reservado a sócios.');
        }

        return $user->member;
    }
}
