<?php

/**
 * Migra classes legadas para app/Modules/{Module}/.
 * Uso: php scripts/migrate-modules.php
 */
$root = dirname(__DIR__);

$migrations = [
    // Members — Models
    ['app/Models/Member.php', 'app/Modules/Members/Models/Member.php', 'App\Models', 'App\Modules\Members\Models', [
        'App\Services\QuotaService' => 'App\Modules\Members\Services\QuotaService',
        'use App\Models\Payment;' => 'use App\Modules\Payments\Models\Payment;',
        'hasMany(Payment::class)' => 'hasMany(Payment::class)',
    ]],
    ['app/Models/QuotaPlan.php', 'app/Modules/Members/Models/QuotaPlan.php', 'App\Models', 'App\Modules\Members\Models', []],
    ['app/Models/Periodicidade.php', 'app/Modules/Members/Models/Periodicidade.php', 'App\Models', 'App\Modules\Members\Models', []],
    ['app/Models/TipoVencimentoQuota.php', 'app/Modules/Members/Models/TipoVencimentoQuota.php', 'App\Models', 'App\Modules\Members\Models', []],
    // Members — Services
    ['app/Services/QuotaService.php', 'app/Modules/Members/Services/QuotaService.php', 'App\Services', 'App\Modules\Members\Services', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
        'use App\Models\Payment;' => 'use App\Modules\Payments\Models\Payment;',
        'use App\Models\QuotaPlan;' => 'use App\Modules\Members\Models\QuotaPlan;',
    ]],
    ['app/Services/MemberAccountService.php', 'app/Modules/Members/Services/MemberAccountService.php', 'App\Services', 'App\Modules\Members\Services', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
    ]],
    ['app/Services/MemberCardRenderer.php', 'app/Modules/Members/Services/MemberCardRenderer.php', 'App\Services', 'App\Modules\Members\Services', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
        'use App\Support\MemberCard' => 'use App\Modules\Members\Support\MemberCard',
    ]],
    ['app/Services/MemberCardViewData.php', 'app/Modules/Members/Services/MemberCardViewData.php', 'App\Services', 'App\Modules\Members\Services', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
        'use App\Support\MemberCard' => 'use App\Modules\Members\Support\MemberCard',
        'use App\Services\QuotaService' => 'use App\Modules\Members\Services\QuotaService',
    ]],
    ['app/Services/MemberCardBatchExporter.php', 'app/Modules/Members/Services/MemberCardBatchExporter.php', 'App\Services', 'App\Modules\Members\Services', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
        'use App\Support\MemberCard' => 'use App\Modules\Members\Support\MemberCard',
    ]],
    ['app/Services/MemberCardBrowsershotExporter.php', 'app/Modules/Members/Services/MemberCardBrowsershotExporter.php', 'App\Services', 'App\Modules\Members\Services', [
        'use App\Support\MemberCard' => 'use App\Modules\Members\Support\MemberCard',
    ]],
    ['app/Services/MemberCardGdExporter.php', 'app/Modules/Members/Services/MemberCardGdExporter.php', 'App\Services', 'App\Modules\Members\Services', [
        'use App\Support\MemberCard' => 'use App\Modules\Members\Support\MemberCard',
    ]],
    // Members — Support
    ['app/Support/MemberCardLayout.php', 'app/Modules/Members/Support/MemberCardLayout.php', 'App\Support', 'App\Modules\Members\Support', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
    ]],
    ['app/Support/MemberCardQrCode.php', 'app/Modules/Members/Support/MemberCardQrCode.php', 'App\Support', 'App\Modules\Members\Support', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
    ]],
    ['app/Support/MemberCardDimensions.php', 'app/Modules/Members/Support/MemberCardDimensions.php', 'App\Support', 'App\Modules\Members\Support', []],
    // Members — Controllers
    ['app/Http/Controllers/MemberCardController.php', 'app/Modules/Members/Http/Controllers/MemberCardController.php', 'App\Http\Controllers', 'App\Modules\Members\Http\Controllers', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
        'use App\Services\MemberCard' => 'use App\Modules\Members\Services\MemberCard',
        'use App\Support\MemberCardLayout' => 'use App\Modules\Members\Support\MemberCardLayout',
    ]],
    ['app/Http/Controllers/MemberValidationController.php', 'app/Modules/Members/Http/Controllers/MemberValidationController.php', 'App\Http\Controllers', 'App\Modules\Members\Http\Controllers', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
        'use App\Services\QuotaService' => 'use App\Modules\Members\Services\QuotaService',
    ]],
    // Payments — Models
    ['app/Models/Payment.php', 'app/Modules/Payments/Models/Payment.php', 'App\Models', 'App\Modules\Payments\Models', [
        'use App\Models\Member;' => 'use App\Modules\Members\Models\Member;',
    ]],
    // Payments — Services
    ['app/Services/PaymentReceiptRenderer.php', 'app/Modules/Payments/Services/PaymentReceiptRenderer.php', 'App\Services', 'App\Modules\Payments\Services', [
        'use App\Models\Payment;' => 'use App\Modules\Payments\Models\Payment;',
    ]],
    // Payments — Controllers
    ['app/Http/Controllers/PaymentReceiptController.php', 'app/Modules/Payments/Http/Controllers/PaymentReceiptController.php', 'App\Http\Controllers', 'App\Modules\Payments\Http\Controllers', [
        'use App\Models\Payment;' => 'use App\Modules\Payments\Models\Payment;',
        'use App\Services\PaymentReceiptRenderer' => 'use App\Modules\Payments\Services\PaymentReceiptRenderer',
    ]],
    ['app/Http/Controllers/Api/MemberPaymentController.php', 'app/Modules/Payments/Http/Controllers/Api/MemberPaymentController.php', 'App\Http\Controllers\Api', 'App\Modules\Payments\Http\Controllers\Api', [
        'use App\Services\PaymentReceiptRenderer' => 'use App\Modules\Payments\Services\PaymentReceiptRenderer',
    ]],
    // Payments — Mail
    ['app/Mail/PaymentReceiptMail.php', 'app/Modules/Payments/Mail/PaymentReceiptMail.php', 'App\Mail', 'App\Modules\Payments\Mail', [
        'use App\Models\Payment;' => 'use App\Modules\Payments\Models\Payment;',
        'use App\Services\PaymentReceiptRenderer' => 'use App\Modules\Payments\Services\PaymentReceiptRenderer',
    ]],
];

$aliases = [
    'app/Models/Member.php' => 'App\Modules\Members\Models\Member',
    'app/Models/QuotaPlan.php' => 'App\Modules\Members\Models\QuotaPlan',
    'app/Models/Periodicidade.php' => 'App\Modules\Members\Models\Periodicidade',
    'app/Models/TipoVencimentoQuota.php' => 'App\Modules\Members\Models\TipoVencimentoQuota',
    'app/Models/Payment.php' => 'App\Modules\Payments\Models\Payment',
    'app/Services/QuotaService.php' => 'App\Modules\Members\Services\QuotaService',
    'app/Services/MemberAccountService.php' => 'App\Modules\Members\Services\MemberAccountService',
    'app/Services/MemberCardRenderer.php' => 'App\Modules\Members\Services\MemberCardRenderer',
    'app/Services/MemberCardViewData.php' => 'App\Modules\Members\Services\MemberCardViewData',
    'app/Services/MemberCardBatchExporter.php' => 'App\Modules\Members\Services\MemberCardBatchExporter',
    'app/Services/MemberCardBrowsershotExporter.php' => 'App\Modules\Members\Services\MemberCardBrowsershotExporter',
    'app/Services/MemberCardGdExporter.php' => 'App\Modules\Members\Services\MemberCardGdExporter',
    'app/Support/MemberCardLayout.php' => 'App\Modules\Members\Support\MemberCardLayout',
    'app/Support/MemberCardQrCode.php' => 'App\Modules\Members\Support\MemberCardQrCode',
    'app/Support/MemberCardDimensions.php' => 'App\Modules\Members\Support\MemberCardDimensions',
    'app/Services/PaymentReceiptRenderer.php' => 'App\Modules\Payments\Services\PaymentReceiptRenderer',
    'app/Http/Controllers/MemberCardController.php' => 'App\Modules\Members\Http\Controllers\MemberCardController',
    'app/Http/Controllers/MemberValidationController.php' => 'App\Modules\Members\Http\Controllers\MemberValidationController',
    'app/Http/Controllers/PaymentReceiptController.php' => 'App\Modules\Payments\Http\Controllers\PaymentReceiptController',
    'app/Http/Controllers/Api/MemberPaymentController.php' => 'App\Modules\Payments\Http\Controllers\Api\MemberPaymentController',
    'app/Mail/PaymentReceiptMail.php' => 'App\Modules\Payments\Mail\PaymentReceiptMail',
];

foreach ($migrations as [$from, $to, $oldNs, $newNs, $extra]) {
    $fromPath = $root.'/'.$from;
    $toPath = $root.'/'.$to;

    if (! is_file($fromPath)) {
        echo "SKIP (missing): {$from}\n";

        continue;
    }

    $dir = dirname($toPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $content = file_get_contents($fromPath);
    $content = preg_replace(
        '/^namespace\s+'.preg_quote($oldNs, '/').';/m',
        'namespace '.$newNs.';',
        $content,
        1,
    );

    foreach ($extra as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    // Controllers precisam de estender Controller base
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
