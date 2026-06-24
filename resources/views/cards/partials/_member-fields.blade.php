<div class="member-card-fields">
    <div class="member-card-label">{{ $layout['card_titulo'] }}</div>

    @if ($layout['show_nome'] ?? true)
        <div class="member-card-name">{{ $member->nome }}</div>
    @endif

    @if (($layout['show_cargo'] ?? true) && filled($member->cargo_cartao))
        <div class="member-card-extra">
            <span style="opacity:.8">{{ $layout['cargo_label'] }}:</span>
            {{ $member->cargo_cartao }}
        </div>
    @endif

    @if ($layout['show_numero'] ?? true)
        <div class="member-card-num">N.º {{ $numeroFormatado }}</div>
    @endif

    @if ($planoLinha)
        <div class="member-card-plano">{{ $planoLinha }}</div>
    @endif

    @if (($layout['show_email'] ?? false) && filled($member->email))
        <div class="member-card-meta">{{ $member->email }}</div>
    @endif

    @if (($layout['show_telefone'] ?? false) && filled($member->telefone))
        <div class="member-card-meta">{{ $member->telefone }}</div>
    @endif

    @if ($layout['show_adesao'] ?? false)
        <div class="member-card-meta">
            Adesão: {{ $member->data_adesao->format('d/m/Y') }}{{ $member->ativo ? '' : ' · Inativo' }}
        </div>
    @endif

    @if ($vencimentoLinha)
        <div class="member-card-due">{{ $vencimentoLinha }}</div>
    @endif
</div>

@if (filled($layout['footer_text'] ?? ''))
    <div class="member-card-footer">{{ $layout['footer_text'] }}</div>
@endif
