<?php

namespace App\Modules\Notifications\Console\Commands;

use App\Enums\QuotaSituationKind;
use App\Models\AppSetting;
use App\Modules\Members\Services\QuotaService;
use App\Modules\Notifications\Mail\QuotaReminderMail;
use App\Support\FeatureRegistry;
use App\Support\Healthcheck;
use App\Support\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendQuotaReminders extends Command
{
    protected $signature = 'gestao:send-quota-reminders {--dry-run : Mostra o que seria enviado sem enviar emails}';

    protected $description = 'Envia lembretes de quota por email aos sócios cuja quota está a vencer e ainda não foi paga.';

    public function handle(QuotaService $quotaService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        try {
            if (! $dryRun && ! FeatureRegistry::enabled('command.quota_reminders')) {
                $this->info('Funcionalidade de lembretes desactivada (Módulos → Funcionalidades). Nada a fazer.');

                return self::SUCCESS;
            }

            if (! $dryRun && ! ModuleRegistry::enabled(ModuleRegistry::LEMBRETES)) {
                $this->info('Módulo de lembretes desactivado (Configuração → Sistema). Nada a fazer.');

                return self::SUCCESS;
            }

            if (! $dryRun && ! AppSetting::bool(AppSetting::LEMBRETES_AUTOMATICOS)) {
                $this->info('Lembretes automáticos desligados (Configuração → Sistema). Nada a fazer.');

                return self::SUCCESS;
            }

            $members = $quotaService->membersWithSituation(QuotaSituationKind::DueSoon);

            $sent = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($members as $member) {
                if (blank($member->email)) {
                    $skipped++;

                    continue;
                }

                $situation = $member->quotaSituation();
                $nextDue = $situation['next_due'] ?? null;

                if ($nextDue === null) {
                    $skipped++;

                    continue;
                }

                if ($member->quota_reminder_due && $member->quota_reminder_due->isSameDay($nextDue)) {
                    $skipped++;

                    continue;
                }

                $dias = (int) ($situation['days_until'] ?? 0);

                if ($dryRun) {
                    $this->line(sprintf('[dry-run] %s <%s> · vence %s (%d dia(s))',
                        $member->nome, $member->email, $nextDue->format('d/m/Y'), $dias));
                    $sent++;

                    continue;
                }

                try {
                    Mail::to($member->email)->send(new QuotaReminderMail($member, $nextDue, $dias));

                    $member->quota_reminder_due = $nextDue;
                    $member->saveQuietly();

                    $sent++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Falha ao enviar lembrete de quota', [
                        'member_id' => $member->id,
                        'email' => $member->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (! $dryRun) {
                activity()
                    ->withProperties([
                        'enviados' => $sent,
                        'ignorados' => $skipped,
                        'falhas' => $failed,
                    ])
                    ->log('Lembretes de quota enviados');
            }

            $this->info(sprintf('Lembretes: %d enviado(s), %d ignorado(s), %d falha(s).', $sent, $skipped, $failed));

            return self::SUCCESS;
        } finally {
            if (! $dryRun) {
                Healthcheck::pingQuotaReminders();
            }
        }
    }
}
