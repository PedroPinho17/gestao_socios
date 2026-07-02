@php
    $club = \App\Models\ClubSetting::current();
    $styles = json_encode(
        ['--tint-color' => $club->panel_primary_color ?? '#10b981'],
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
@endphp

<pwa-install
    manifest-url="/site.webmanifest"
    name="{{ $club->nome_clube }}"
    description="Backoffice de gestão de sócios"
    styles='{!! $styles !!}'
    use-local-storage
></pwa-install>
