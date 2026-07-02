<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureModuleEnabled;
use App\Support\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class EnsureModuleEnabledTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    public function test_api_requests_receive_json_forbidden_when_module_disabled(): void
    {
        $this->setModuleEnabled(ModuleRegistry::RELATORIOS, false);

        $middleware = app(EnsureModuleEnabled::class);
        $request = Request::create('/api/example', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $middleware->handle($request, fn () => response('ok'), ModuleRegistry::RELATORIOS);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('funcionalidade', json_decode($response->getContent(), true)['message']);
    }

    public function test_web_requests_abort_not_found_when_module_disabled(): void
    {
        $this->setModuleEnabled(ModuleRegistry::RELATORIOS, false);

        $middleware = app(EnsureModuleEnabled::class);
        $request = Request::create('/relatorios/socios-em-atraso.pdf', 'GET');

        $this->expectException(NotFoundHttpException::class);

        $middleware->handle($request, fn () => response('ok'), ModuleRegistry::RELATORIOS);
    }

    public function test_staff_report_route_returns_not_found_when_reports_module_disabled(): void
    {
        $this->setModuleEnabled(ModuleRegistry::RELATORIOS, false);
        $staff = $this->createStaffUser();

        $this->actingAs($staff)
            ->get(route('reports.overdue.pdf'))
            ->assertNotFound();
    }
}
