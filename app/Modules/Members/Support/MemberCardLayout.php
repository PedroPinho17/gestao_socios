<?php

namespace App\Modules\Members\Support;

use App\Models\ClubSetting;

class MemberCardLayout
{
    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'template' => 'crc_vale',
            'available_templates' => self::allTemplateKeys(),
            'orientation' => 'horizontal',
            'logo_position' => 'left',
            'photo_position' => 'right',
            'photo_shape' => 'rounded',
            'font_family' => 'system',
            'numero_prefix' => '',
            'footer_text' => '',
            'verso_text' => '',
            'card_motto' => 'TRADIÇÃO • ESPORTE • CULTURA',
            'card_slogan' => 'Juntos Somos Mais Fortes',
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

        $layout['available_templates'] = self::normalizeAvailableTemplates($layout['available_templates'] ?? null);
        $layout['template'] = self::ensureTemplateAllowed(
            (string) ($layout['template'] ?? 'classic'),
            $layout['available_templates'],
        );

        return $layout;
    }

    /**
     * @param  array<string, mixed>  $layout
     */
    public static function hasVerso(array $layout): bool
    {
        if (($layout['template'] ?? '') === 'crc_vale') {
            return true;
        }

        return filled($layout['verso_text'] ?? '') || ($layout['show_qr_verso'] ?? false);
    }

    /**
     * Nome do template Blade do verso (sem prefixo cards.templates.).
     */
    public static function versoTemplate(array $layout): string
    {
        $name = ($layout['template'] ?? '') === 'crc_vale' ? 'crc_vale_verso' : 'verso';

        return view()->exists('cards.templates.'.$name) ? $name : 'verso';
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
     * Catálogo completo de modelos instalados (código).
     *
     * @return array<string, array{label: string, group: string, description: string}>
     */
    public static function catalog(): array
    {
        return [
            'classic' => [
                'label' => 'Clássico',
                'group' => 'base',
                'description' => 'Modelo base com gradiente e logótipo.',
            ],
            'modern' => [
                'label' => 'Moderno',
                'group' => 'base',
                'description' => 'Modelo base com faixas e tipografia destacada.',
            ],
            'minimal' => [
                'label' => 'Minimal',
                'group' => 'base',
                'description' => 'Modelo base limpo, ideal para demonstrações.',
            ],
            'crc_vale' => [
                'label' => 'CRC VALE (oficial)',
                'group' => 'clube',
                'description' => 'Layout personalizado do clube com frente e verso.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function allTemplateKeys(): array
    {
        return array_keys(self::catalog());
    }

    /**
     * @return list<string>
     */
    public static function defaultAvailableTemplates(): array
    {
        return self::allTemplateKeys();
    }

    /**
     * @return list<string>
     */
    public static function baseTemplateKeys(): array
    {
        return array_values(array_filter(
            self::allTemplateKeys(),
            fn (string $key): bool => (self::catalog()[$key]['group'] ?? '') === 'base',
        ));
    }

    /**
     * @return list<string>
     */
    public static function normalizeAvailableTemplates(mixed $templates): array
    {
        if (! is_array($templates) || $templates === []) {
            return self::defaultAvailableTemplates();
        }

        $valid = array_values(array_intersect(
            array_map('strval', $templates),
            self::allTemplateKeys(),
        ));

        return $valid !== [] ? $valid : self::defaultAvailableTemplates();
    }

    /**
     * @param  list<string>  $available
     */
    public static function ensureTemplateAllowed(string $template, array $available): string
    {
        $available = self::normalizeAvailableTemplates($available);

        if (in_array($template, $available, true)) {
            return $template;
        }

        return $available[0];
    }

    /**
     * @param  list<string>|null  $onlyKeys
     * @return array<string, string>
     */
    public static function templateOptions(?array $onlyKeys = null): array
    {
        $keys = self::normalizeAvailableTemplates($onlyKeys ?? self::allTemplateKeys());
        $options = [];

        foreach ($keys as $key) {
            $options[$key] = self::catalog()[$key]['label'];
        }

        return $options;
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
