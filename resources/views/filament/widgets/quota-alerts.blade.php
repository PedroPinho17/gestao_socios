<x-filament-widgets::widget>
    <x-filament::section>
        @if ($overdue->isEmpty() && $dueSoon->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Não há quotas em atraso nem a vencer nos próximos {{ $dueSoonDays }} dias.
            </p>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                @if ($overdue->isNotEmpty())
                    <div class="rounded-xl border border-danger-200 bg-danger-50/50 p-4 dark:border-danger-500/30 dark:bg-danger-500/10">
                        <h3 class="font-semibold text-danger-700 dark:text-danger-400">
                            Quotas em atraso ({{ $overdueTotal }})
                        </h3>
                        <ul class="mt-3 divide-y divide-danger-200/80 dark:divide-danger-500/20">
                            @foreach ($overdue as $row)
                                <li class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                                    <a
                                        href="{{ \App\Filament\Resources\Members\MemberResource::getUrl('edit', ['record' => $row['id']]) }}"
                                        class="font-medium text-danger-800 hover:underline dark:text-danger-300"
                                    >
                                        {{ $row['nome'] }}
                                    </a>
                                    <span class="text-danger-700 dark:text-danger-400">
                                        {{ $row['situation']['days_overdue'] }} dia(s) ·
                                        venc. {{ $quotaService->formatDatePT($row['situation']['next_due']) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($dueSoon->isNotEmpty())
                    <div class="rounded-xl border border-warning-200 bg-warning-50/50 p-4 dark:border-warning-500/30 dark:bg-warning-500/10">
                        <h3 class="font-semibold text-warning-800 dark:text-warning-400">
                            Vence nos próximos {{ $dueSoonDays }} dias ({{ $dueSoonTotal }})
                        </h3>
                        <ul class="mt-3 divide-y divide-warning-200/80 dark:divide-warning-500/20">
                            @foreach ($dueSoon as $row)
                                <li class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                                    <a
                                        href="{{ \App\Filament\Resources\Members\MemberResource::getUrl('edit', ['record' => $row['id']]) }}"
                                        class="font-medium text-warning-900 hover:underline dark:text-warning-200"
                                    >
                                        {{ $row['nome'] }}
                                    </a>
                                    <span class="text-warning-800 dark:text-warning-300">
                                        em {{ $row['situation']['days_until'] }} dia(s) ·
                                        {{ $quotaService->formatDatePT($row['situation']['next_due']) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
