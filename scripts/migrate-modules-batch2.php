<?php

/**
 * Migra Reports, Auth, Files e Notifications para app/Modules/.
 * Uso: php scripts/migrate-modules-batch2.php
 */
$root = dirname(__DIR__);

$common = [
    'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
    'use App\Services\QuotaService' => 'use App\Modules\Members\Services\QuotaService',
    'use App\Models\Payment;' => 'use App\Modules\Payments\Models\Payment;',
    'use App\Models\User;' => 'use App\Modules\Auth\Models\User;',
    'use App\Models\Permissao;' => 'use App\Modules\Auth\Models\Permissao;',
];

$migrations = [
    ['app/Services/OverdueMembersReportService.php', 'app/Modules/Reports/Services/OverdueMembersReportService.php', 'App\Services', 'App\Modules\Reports\Services', $common],
    ['app/Services/PayingMembersReportService.php', 'app/Modules/Reports/Services/PayingMembersReportService.php', 'App\Services', 'App\Modules\Reports\Services', $common],
    ['app/Http/Controllers/OverdueMembersReportController.php', 'app/Modules/Reports/Http/Controllers/OverdueMembersReportController.php', 'App\Http\Controllers', 'App\Modules\Reports\Http\Controllers', [
        'use App\Services\OverdueMembersReportService' => 'use App\Modules\Reports\Services\OverdueMembersReportService',
    ]],
    ['app/Http/Controllers/PayingMembersReportController.php', 'app/Modules/Reports/Http/Controllers/PayingMembersReportController.php', 'App\Http\Controllers', 'App\Modules\Reports\Http\Controllers', [
        'use App\Services\PayingMembersReportService' => 'use App\Modules\Reports\Services\PayingMembersReportService',
    ]],
    ['app/Http/Controllers/Api/MemberAuthController.php', 'app/Modules/Auth/Http/Controllers/Api/MemberAuthController.php', 'App\Http\Controllers\Api', 'App\Modules\Auth\Http\Controllers\Api', $common],
    ['app/Http/Controllers/Api/MemberQuotaController.php', 'app/Modules/Auth/Http/Controllers/Api/MemberQuotaController.php', 'App\Http\Controllers\Api', 'App\Modules\Auth\Http\Controllers\Api', $common],
    ['app/Http/Middleware/EnsureMemberPasswordChanged.php', 'app/Modules/Auth/Http/Middleware/EnsureMemberPasswordChanged.php', 'App\Http\Middleware', 'App\Modules\Auth\Http\Middleware', []],
    ['app/Models/User.php', 'app/Modules/Auth/Models/User.php', 'App\Models', 'App\Modules\Auth\Models', $common],
    ['app/Models/Permissao.php', 'app/Modules/Auth/Models/Permissao.php', 'App\Models', 'App\Modules\Auth\Models', $common],
    ['app/Http/Controllers/SecureFileController.php', 'app/Modules/Files/Http/Controllers/SecureFileController.php', 'App\Http\Controllers', 'App\Modules\Files\Http\Controllers', $common],
    ['app/Mail/QuotaReminderMail.php', 'app/Modules/Notifications/Mail/QuotaReminderMail.php', 'App\Mail', 'App\Modules\Notifications\Mail', $common],
    ['app/Mail/ClubAnnouncementMail.php', 'app/Modules/Notifications/Mail/ClubAnnouncementMail.php', 'App\Mail', 'App\Modules\Notifications\Mail', $common],
    ['app/Console/Commands/SendQuotaReminders.php', 'app/Modules/Notifications/Console/Commands/SendQuotaReminders.php', 'App\Console\Commands', 'App\Modules\Notifications\Console\Commands', array_merge($common, [
        'use App\Mail\QuotaReminderMail' => 'use App\Modules\Notifications\Mail\QuotaReminderMail',
    ])],
];

$aliases = [
    'app/Services/OverdueMembersReportService.php' => 'App\Modules\Reports\Services\OverdueMembersReportService',
    'app/Services/PayingMembersReportService.php' => 'App\Modules\Reports\Services\PayingMembersReportService',
    'app/Http/Controllers/OverdueMembersReportController.php' => 'App\Modules\Reports\Http\Controllers\OverdueMembersReportController',
    'app/Http/Controllers/PayingMembersReportController.php' => 'App\Modules\Reports\Http\Controllers\PayingMembersReportController',
    'app/Http/Controllers/Api/MemberAuthController.php' => 'App\Modules\Auth\Http\Controllers\Api\MemberAuthController',
    'app/Http/Controllers/Api/MemberQuotaController.php' => 'App\Modules\Auth\Http\Controllers\Api\MemberQuotaController',
    'app/Http/Middleware/EnsureMemberPasswordChanged.php' => 'App\Modules\Auth\Http\Middleware\EnsureMemberPasswordChanged',
    'app/Models/User.php' => 'App\Modules\Auth\Models\User',
    'app/Models/Permissao.php' => 'App\Modules\Auth\Models\Permissao',
    'app/Http/Controllers/SecureFileController.php' => 'App\Modules\Files\Http\Controllers\SecureFileController',
    'app/Mail/QuotaReminderMail.php' => 'App\Modules\Notifications\Mail\QuotaReminderMail',
    'app/Mail/ClubAnnouncementMail.php' => 'App\Modules\Notifications\Mail\ClubAnnouncementMail',
    'app/Console/Commands/SendQuotaReminders.php' => 'App\Modules\Notifications\Console\Commands\SendQuotaReminders',
];

foreach ($migrations as [$from, $to, $oldNs, $newNs, $extra]) {
    $fromPath = $root.'/'.$from;
    $toPath = $root.'/'.$to;

    if (! is_file($fromPath)) {
        echo "SKIP (missing): {$from}\n";

        continue;
    }

    $content = file_get_contents($fromPath);

    if (str_contains($content, '@deprecated')) {
        echo "SKIP (alias): {$from}\n";

        continue;
    }

    $dir = dirname($toPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $content = preg_replace(
        '/^namespace\s+'.preg_quote($oldNs, '/').';/m',
        'namespace '.$newNs.';',
        $content,
        1,
    );

    foreach ($extra as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if (str_contains($to, 'Http/Controllers/') && ! str_contains($content, 'use App\Http\Controllers\Controller;')) {
        $content = preg_replace(
            '/^(namespace [^;]+;\n\n)/m',
            "$1use App\Http\Controllers\Controller;\n",
            $content,
            1,
        );
    }

    file_put_contents($toPath, $content);
    echo "OK: {$to}\n";
}

foreach ($aliases as $path => $target) {
    $full = $root.'/'.$path;
    $parts = explode('\\', $target);
    $short = end($parts);
    $oldNs = dirname(str_replace('/', '\\', $path));
    $oldNs = str_starts_with($oldNs, 'app\\') ? 'App\\'.substr($oldNs, 4) : $oldNs;
    $oldNs = str_replace('/', '\\', $oldNs);

    $stub = <<<PHP
<?php

namespace {$oldNs};

/**
 * @deprecated Use {@see \\{$target}}
 */
class {$short} extends \\{$target}
{
}

PHP;
    file_put_contents($full, $stub);
    echo "ALIAS: {$path}\n";
}

echo "Done.\n";
