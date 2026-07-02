<?php

namespace App\Modules\Core\Filament;

use App\Modules\Core\ModuleCatalog;

final class FilamentRegistrar
{
    /**
     * @return list<class-string>
     */
    public static function resources(): array
    {
        return self::collect('filamentResources');
    }

    /**
     * @return list<class-string>
     */
    public static function pages(): array
    {
        return self::collect('filamentPages');
    }

    /**
     * @return list<class-string>
     */
    public static function widgets(): array
    {
        return self::collect('filamentWidgets');
    }

    /**
     * @return list<class-string>
     */
    private static function collect(string $method): array
    {
        $items = [];

        foreach (ModuleCatalog::providers() as $provider) {
            if (! method_exists($provider, $method)) {
                continue;
            }

            /** @var list<class-string> $chunk */
            $chunk = $provider::$method();
            $items = array_merge($items, $chunk);
        }

        return array_values(array_unique($items));
    }
}
