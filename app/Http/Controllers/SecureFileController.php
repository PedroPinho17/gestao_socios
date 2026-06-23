<?php

namespace App\Http\Controllers;

use App\Models\ClubSetting;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileController extends Controller
{
    public function memberPhoto(Member $member): StreamedResponse
    {
        abort_unless($member->foto_path, 404);
        abort_unless(Storage::disk('local')->exists($member->foto_path), 404);

        return Storage::disk('local')->response($member->foto_path);
    }

    public function clubLogo(): StreamedResponse
    {
        $settings = ClubSetting::current();
        abort_unless($settings->logo_path, 404);
        abort_unless(Storage::disk('local')->exists($settings->logo_path), 404);

        return Storage::disk('local')->response($settings->logo_path);
    }
}
