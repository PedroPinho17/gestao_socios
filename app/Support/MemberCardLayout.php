<?php

namespace App\Support;

use App\Models\ClubSetting;

final class MemberCardLayout
{
    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'template' => 'classic',
            'orientation' => 'horizontal',
            'logo_position' => 'left',
            'photo_position' => 'right',
            'photo_shape' => 'rounded',
            'font_family' => 'system',
            'numero_prefix' => '',
            'footer_text' => '',
            'verso_text' => '',
            'show_qr_verso' => false,
            'qr_content' => 'validacao',
            'show_border' => false,
            'border_color' => '#ffffff',
            'border_width' => 1,
            'text_color' => '#ffffff',
            'show_numero' => true,
            'show_nome' => true,
            'show_foto' => true,
            'show_plano' => false,
            'show_adesao' => false,
            'show_validade' => true,
            'show_cargo' => true,
            'show_email' => false,
            'show_telefone' => false,
        ];
    }

    /**
     * @param  object|array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function defaultsFromRow(object|array $row): array
    {
        $row = (array) $row;

        return array_merge(self::defaults(), [
            'show_validade' => (bool) ($row['show_proximo_vencimento'] ?? true),
            'show_cargo' => (bool) ($row['show_cargo'] ?? true),
            'show_email' => (bool) ($row['show_email'] ?? false),
            'show_telefone' => (bool) ($row['show_telefone'] ?? false),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $overrides
     * @return array<string, mixed>
     */
    public static function resolve(ClubSetting $settings, ?array $overrides = null): array
    {
        $stored = is_array($settings->card_layout) ? $settings->card_layout : [];

        $layout = array_merge(self::defaultsFromRow($settings), $stored);

        if ($overrides !== null) {
            $nested = $overrides['card_layout'] ?? null;
            if (is_array($nested)) {
                $layout = array_merge($layout, $nested);
            }
            foreach (self::defaults() as $key => $_) {
                if (array_key_exists($key, $overrides)) {
                    $layout[$key] = $overrides[$key];
                }
            }
        }

        $layout['gradient_from'] = $settings->card_gradient_from ?? '#0f766e';
        $layout['gradient_to'] = $settings->card_gradient_to ?? '#0f172a';
        $layout['accent_color'] = $settings->card_accent_color ?? '#d1fae5';
        $layout['card_titulo'] = $settings->card_titulo ?? 'Sócio';
        $layout['cargo_label'] = $settings->card_campo_extra_label ?? 'Cargo';
        $layout['nome_clube'] = $settings->nome_clube ?? 'O meu clube';

        return $layout;
    }

    /**
     * @param  array<string, mixed>  $layout
     */
    public static function hasVerso(array $layout): bool
    {
        return filled($layout['verso_text'] ?? '') || ($layout['show_qr_verso'] ?? false);
    }

    public static function fontStack(string $family): string
    {
        return match ($family) {
            'serif' => 'Georgia, "Times New Roman", serif',
            'mono' => 'ui-monospace, "Cascadia Code", Consolas, monospace',
            default => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function templateOptions(): array
    {
        return [
            'classic' => 'Clássico',
            'modern' => 'Moderno',
            'minimal' => 'Minimal',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function logoPositionOptions(): array
    {
        return [
            'left' => 'Esquerda',
            'center' => 'Centro',
            'right' => 'Direita',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function photoShapeOptions(): array
    {
        return [
            'rounded' => 'Cantos arredondados',
            'circle' => 'Circular',
            'square' => 'Quadrado',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function fontOptions(): array
    {
        return [
            'system' => 'Sans-serif (sistema)',
            'serif' => 'Serif',
            'mono' => 'Monospace',
        ];
    }
}
