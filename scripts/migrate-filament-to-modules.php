<?php

/**
 * Move Filament UI from app/Filament to app/Modules/{Module}/Filament.
 * Run: php scripts/migrate-filament-to-modules.php
 */
$root = dirname(__DIR__);

/** @var list<array{from: string, to: string, ns_from: string, ns_to: string}> */
$moves = [
    [
        'from' => 'app/Filament/Concerns',
        'to' => 'app/Modules/Core/Filament/Concerns',
        'ns_from' => 'App\\Filament\\Concerns',
        'ns_to' => 'App\\Modules\\Core\\Filament\\Concerns',
    ],
    [
        'from' => 'app/Filament/Pages/Dashboard.php',
        'to' => 'app/Modules/Core/Filament/Pages/Dashboard.php',
        'ns_from' => 'App\\Filament\\Pages',
        'ns_to' => 'App\\Modules\\Core\\Filament\\Pages',
    ],
    [
        'from' => 'app/Filament/Pages/ChangeRequiredPassword.php',
        'to' => 'app/Modules/Core/Filament/Pages/ChangeRequiredPassword.php',
        'ns_from' => 'App\\Filament\\Pages',
        'ns_to' => 'App\\Modules\\Core\\Filament\\Pages',
    ],
    [
        'from' => 'app/Filament/Widgets',
        'to' => 'app/Modules/Members/Filament/Widgets',
        'ns_from' => 'App\\Filament\\Widgets',
        'ns_to' => 'App\\Modules\\Members\\Filament\\Widgets',
    ],
    [
        'from' => 'app/Filament/Resources/Members',
        'to' => 'app/Modules/Members/Filament/Resources/Members',
        'ns_from' => 'App\\Filament\\Resources\\Members',
        'ns_to' => 'App\\Modules\\Members\\Filament\\Resources\\Members',
    ],
    [
        'from' => 'app/Filament/Resources/QuotaPlans',
        'to' => 'app/Modules/Members/Filament/Resources/QuotaPlans',
        'ns_from' => 'App\\Filament\\Resources\\QuotaPlans',
        'ns_to' => 'App\\Modules\\Members\\Filament\\Resources\\QuotaPlans',
    ],
    [
        'from' => 'app/Filament/Clusters',
        'to' => 'app/Modules/Catalogos/Filament/Clusters',
        'ns_from' => 'App\\Filament\\Clusters',
        'ns_to' => 'App\\Modules\\Catalogos\\Filament\\Clusters',
    ],
    [
        'from' => 'app/Filament/Resources/Periodicidades',
        'to' => 'app/Modules/Catalogos/Filament/Resources/Periodicidades',
        'ns_from' => 'App\\Filament\\Resources\\Periodicidades',
        'ns_to' => 'App\\Modules\\Catalogos\\Filament\\Resources\\Periodicidades',
    ],
    [
        'from' => 'app/Filament/Resources/TiposVencimentoQuota',
        'to' => 'app/Modules/Catalogos/Filament/Resources/TiposVencimentoQuota',
        'ns_from' => 'App\\Filament\\Resources\\TiposVencimentoQuota',
        'ns_to' => 'App\\Modules\\Catalogos\\Filament\\Resources\\TiposVencimentoQuota',
    ],
    [
        'from' => 'app/Filament/Resources/Users',
        'to' => 'app/Modules/Auth/Filament/Resources/Users',
        'ns_from' => 'App\\Filament\\Resources\\Users',
        'ns_to' => 'App\\Modules\\Auth\\Filament\\Resources\\Users',
    ],
    [
        'from' => 'app/Filament/Resources/ActivityLogs',
        'to' => 'app/Modules/Audit/Filament/Resources/ActivityLogs',
        'ns_from' => 'App\\Filament\\Resources\\ActivityLogs',
        'ns_to' => 'App\\Modules\\Audit\\Filament\\Resources\\ActivityLogs',
    ],
    [
        'from' => 'app/Filament/Pages/CommunicationsPage.php',
        'to' => 'app/Modules/Notifications/Filament/Pages/CommunicationsPage.php',
        'ns_from' => 'App\\Filament\\Pages',
        'ns_to' => 'App\\Modules\\Notifications\\Filament\\Pages',
    ],
    [
        'from' => 'app/Filament/Pages/ClubSettingsPage.php',
        'to' => 'app/Modules/Settings/Filament/Pages/ClubSettingsPage.php',
        'ns_from' => 'App\\Filament\\Pages',
        'ns_to' => 'App\\Modules\\Settings\\Filament\\Pages',
    ],
    [
        'from' => 'app/Filament/Pages/SystemSettingsPage.php',
        'to' => 'app/Modules/Settings/Filament/Pages/SystemSettingsPage.php',
        'ns_from' => 'App\\Filament\\Pages',
        'ns_to' => 'App\\Modules\\Settings\\Filament\\Pages',
    ],
    [
        'from' => 'app/Filament/Resources/Modules',
        'to' => 'app/Modules/Settings/Filament/Resources/Modules',
        'ns_from' => 'App\\Filament\\Resources\\Modules',
        'ns_to' => 'App\\Modules\\Settings\\Filament\\Resources\\Modules',
    ],
    [
        'from' => 'app/Filament/Resources/ModuleFeatures',
        'to' => 'app/Modules/Settings/Filament/Resources/ModuleFeatures',
        'ns_from' => 'App\\Filament\\Resources\\ModuleFeatures',
        'ns_to' => 'App\\Modules\\Settings\\Filament\\Resources\\ModuleFeatures',
    ],
];

/** @var array<string, string> */
$nsReplacements = [
    'App\\Filament\\Concerns' => 'App\\Modules\\Core\\Filament\\Concerns',
    'App\\Filament\\Pages' => 'App\\Modules\\Core\\Filament\\Pages', // overridden per-file below
    'App\\Filament\\Widgets' => 'App\\Modules\\Members\\Filament\\Widgets',
    'App\\Filament\\Resources\\Members' => 'App\\Modules\\Members\\Filament\\Resources\\Members',
    'App\\Filament\\Resources\\QuotaPlans' => 'App\\Modules\\Members\\Filament\\Resources\\QuotaPlans',
    'App\\Filament\\Clusters' => 'App\\Modules\\Catalogos\\Filament\\Clusters',
    'App\\Filament\\Resources\\Periodicidades' => 'App\\Modules\\Catalogos\\Filament\\Resources\\Periodicidades',
    'App\\Filament\\Resources\\TiposVencimentoQuota' => 'App\\Modules\\Catalogos\\Filament\\Resources\\TiposVencimentoQuota',
    'App\\Filament\\Resources\\Users' => 'App\\Modules\\Auth\\Filament\\Resources\\Users',
    'App\\Filament\\Resources\\ActivityLogs' => 'App\\Modules\\Audit\\Filament\\Resources\\ActivityLogs',
    'App\\Filament\\Resources\\Modules' => 'App\\Modules\\Settings\\Filament\\Resources\\Modules',
    'App\\Filament\\Resources\\ModuleFeatures' => 'App\\Modules\\Settings\\Filament\\Resources\\ModuleFeatures',
];

$pageOverrides = [
    'CommunicationsPage.php' => 'App\\Modules\\Notifications\\Filament\\Pages',
    'ClubSettingsPage.php' => 'App\\Modules\\Settings\\Filament\\Pages',
    'SystemSettingsPage.php' => 'App\\Modules\\Settings\\Filament\\Pages',
];

function collectFiles(string $path): array
{
    if (is_file($path)) {
        return [$path];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function replaceNamespaces(string $content, array $replacements): string
{
    uksort($replacements, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

    return str_replace(array_keys($replacements), array_values($replacements), $content);
}

$moved = 0;

foreach ($moves as $move) {
    $source = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $move['from']);

    if (! file_exists($source)) {
        echo "SKIP (missing): {$move['from']}\n";

        continue;
    }

    foreach (collectFiles($source) as $file) {
        $relative = ltrim(str_replace($source, '', is_dir($source) ? $file : dirname($source)), DIRECTORY_SEPARATOR);

        if (is_file($source)) {
            $relative = basename($source);
        }

        $destDir = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, dirname($move['to']));
        $dest = is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $move['from']))
            ? $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $move['to'])
            : $destDir.DIRECTORY_SEPARATOR.$relative;

        if (! is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0777, true);
        }

        $content = file_get_contents($file);
        $content = replaceNamespaces($content, $nsReplacements);

        $basename = basename($file);
        $targetNs = $move['ns_to'];

        if (isset($pageOverrides[$basename])) {
            $content = preg_replace(
                '/namespace App\\\\Modules\\\\Core\\\\Filament\\\\Pages;/',
                'namespace '.$pageOverrides[$basename].';',
                $content,
            ) ?? $content;
            $targetNs = $pageOverrides[$basename];
        } else {
            $content = preg_replace(
                '/namespace '.preg_quote($move['ns_from'], '/').'(?:\\\\[A-Za-z0-9_]+)*;/',
                'namespace '.$targetNs.(str_contains($relative, DIRECTORY_SEPARATOR)
                    ? '\\'.str_replace(DIRECTORY_SEPARATOR, '\\', dirname($relative))
                    : '').';',
                $content,
                1,
            ) ?? $content;
        }

        file_put_contents($dest, $content);
        $moved++;
        echo "MOVED: {$file} -> {$dest}\n";
    }
}

echo "\nMoved {$moved} files.\n";

// Patch remaining references in project PHP and blade files
$extensions = ['php', 'blade.php'];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
);

$patched = 0;

foreach ($iterator as $file) {
    if ($file->getPathname() === __FILE__) {
        continue;
    }

    $ext = $file->getExtension();
    if ($ext === 'php' && str_ends_with($file->getFilename(), '.blade.php')) {
        $ext = 'blade.php';
    }

    if (! in_array($ext, ['php', 'blade.php'], true)) {
        continue;
    }

    if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR)) {
        continue;
    }

    if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR)) {
        continue;
    }

    if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR)) {
        continue;
    }

    $content = file_get_contents($file->getPathname());
    $updated = replaceNamespaces($content, $nsReplacements);

    // Page-specific namespace fixes in references
    $pageRefMap = [
        'App\\Modules\\Core\\Filament\\Pages\\CommunicationsPage' => 'App\\Modules\\Notifications\\Filament\\Pages\\CommunicationsPage',
        'App\\Modules\\Core\\Filament\\Pages\\ClubSettingsPage' => 'App\\Modules\\Settings\\Filament\\Pages\\ClubSettingsPage',
        'App\\Modules\\Core\\Filament\\Pages\\SystemSettingsPage' => 'App\\Modules\\Settings\\Filament\\Pages\\SystemSettingsPage',
    ];

    $updated = str_replace(array_keys($pageRefMap), array_values($pageRefMap), $updated);

    if ($updated !== $content) {
        file_put_contents($file->getPathname(), $updated);
        $patched++;
    }
}

echo "Patched {$patched} files with updated references.\n";
