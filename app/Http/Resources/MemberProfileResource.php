<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class MemberProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $member = $this->member;
        $plan = $member?->quotaPlan;

        return [
            'nome' => $member?->nome,
            'numero' => $member?->numero,
            'email' => $this->email,
            'must_change_password' => $this->must_change_password,
            'plano' => $plan ? [
                'nome' => $plan->nome,
                'valor' => (float) $plan->valor,
                'periodicidade' => $plan->periodicidade?->nome,
            ] : null,
        ];
    }
}
