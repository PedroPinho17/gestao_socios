<?php

namespace App\Modules\Core;

use App\Modules\Audit\AuditServiceProvider;
use App\Modules\Auth\AuthServiceProvider;
use App\Modules\Catalogos\CatalogosServiceProvider;
use App\Modules\Events\EventsServiceProvider;
use App\Modules\Files\FilesServiceProvider;
use App\Modules\Members\MembersServiceProvider;
use App\Modules\Notifications\NotificationsServiceProvider;
use App\Modules\Payments\PaymentsServiceProvider;
use App\Modules\Reports\ReportsServiceProvider;
use App\Modules\Reservations\ReservationsServiceProvider;
use App\Modules\Settings\SettingsServiceProvider;

/**
 * Mapa dos módulos de domínio e migração gradual do código legado.
 *
 * Estrutura por módulo (app/Modules/{Name}/):
 * - Http/Controllers   — endpoints HTTP
 * - Services           — regras de negócio
 * - DTOs               — objetos de transferência (readonly)
 * - Models             — Eloquent (equivalente à camada de dados Prisma)
 * - routes.php         — rotas do módulo (opcional)
 *
 * Testes espelhados: tests/Modules/{Name}/
 *
 * Código novo deve usar sempre o namespace do módulo.
 * Aliases @deprecated em app/ existem até a migração estar completa.
 */
final class ModuleCatalog
{
    public const CATALOGOS = 'Catalogos';

    public const AUTH = 'Auth';

    public const MEMBERS = 'Members';

    public const PAYMENTS = 'Payments';

    public const EVENTS = 'Events';

    public const RESERVATIONS = 'Reservations';

    public const NOTIFICATIONS = 'Notifications';

    public const FILES = 'Files';

    public const REPORTS = 'Reports';

    public const AUDIT = 'Audit';

    public const SETTINGS = 'Settings';

    /**
     * @return list<class-string<ModuleServiceProvider>>
     */
    public static function providers(): array
    {
        return [
            CoreServiceProvider::class,
            AuthServiceProvider::class,
            MembersServiceProvider::class,
            CatalogosServiceProvider::class,
            PaymentsServiceProvider::class,
            EventsServiceProvider::class,
            ReservationsServiceProvider::class,
            NotificationsServiceProvider::class,
            FilesServiceProvider::class,
            ReportsServiceProvider::class,
            AuditServiceProvider::class,
            SettingsServiceProvider::class,
        ];
    }

    /**
     * Guia de migração: código legado → módulo alvo.
     *
     * @return array<string, list<string>>
     */
    public static function migrationGuide(): array
    {
        return [
            self::AUTH => [
                'app/Modules/Auth/Filament/Resources/Users/',
            ],
            self::MEMBERS => [
                'app/Modules/Members/Filament/',
            ],
            self::CATALOGOS => [
                'app/Modules/Catalogos/Filament/',
            ],
            self::PAYMENTS => [],
            self::REPORTS => [],
            self::FILES => [],
            self::NOTIFICATIONS => [
                'app/Modules/Notifications/Filament/Pages/CommunicationsPage.php',
            ],
            self::AUDIT => [
                'app/Modules/Audit/Filament/Resources/ActivityLogs/',
            ],
            self::SETTINGS => [
                'app/Models/ClubSetting.php',
                'app/Models/AppSetting.php',
                'app/Models/Module.php',
                'app/Support/ModuleRegistry.php',
                'app/Support/FeatureRegistry.php',
                'app/Modules/Settings/Filament/',
            ],
            self::EVENTS => [],
            self::RESERVATIONS => [],
        ];
    }
}
