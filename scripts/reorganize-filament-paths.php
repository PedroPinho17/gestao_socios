<?php

$root = dirname(__DIR__);

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.'/app/Modules', FilesystemIterator::SKIP_DOTS),
);

$moved = 0;

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    if (! str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR)) {
        continue;
    }

    $content = file_get_contents($file->getPathname());

    if (! preg_match('/^namespace\s+([^;]+);/m', $content, $matches)) {
        continue;
    }

    $class = null;
    if (preg_match('/^class\s+(\w+)/m', $content, $classMatch)) {
        $class = $classMatch[1];
    } elseif (preg_match('/^trait\s+(\w+)/m', $content, $classMatch)) {
        $class = $classMatch[1];
    }

    if ($class === null) {
        continue;
    }

    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $matches[1]).DIRECTORY_SEPARATOR.$class.'.php';
    $target = $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$relativePath;

    if (realpath($file->getPathname()) === realpath($target)) {
        continue;
    }

    if (! is_dir(dirname($target))) {
        mkdir(dirname($target), 0777, true);
    }

    if (file_exists($target)) {
        unlink($target);
    }

    rename($file->getPathname(), $target);
    $moved++;
    echo "REORG: {$file->getPathname()} -> {$target}\n";
}

echo "\nReorganized {$moved} files.\n";
