<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubBrandingResource;
use App\Support\ClubBranding;

class ClubBrandingController extends Controller
{
    public function show(): ClubBrandingResource
    {
        return new ClubBrandingResource(ClubBranding::forMemberArea());
    }
}
