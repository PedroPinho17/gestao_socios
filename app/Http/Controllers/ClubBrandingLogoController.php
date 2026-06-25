<?php

namespace App\Http\Controllers;

use App\Support\ClubBranding;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClubBrandingLogoController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        if (ClubBranding::hasLogo()) {
            return response()->file(ClubBranding::logoPublicPath());
        }

        $path = ClubBranding::settings()->logo_path;
        abort_unless($path, 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
