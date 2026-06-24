<?php

namespace App\Support;

final class MemberCardDimensions
{
    public const WIDTH_MM = 85.6;

    public const HEIGHT_MM = 53.98;

    public const BLEED_MM = 3;

    public const DPI = 300;

    public const MM_TO_PT = 2.834645669;

    public static function widthMm(bool $withBleed = false): float
    {
        return self::WIDTH_MM + ($withBleed ? self::BLEED_MM * 2 : 0);
    }

    public static function heightMm(bool $withBleed = false): float
    {
        return self::HEIGHT_MM + ($withBleed ? self::BLEED_MM * 2 : 0);
    }

    public static function widthPx(int $dpi = self::DPI, bool $withBleed = false): int
    {
        return (int) round(self::widthMm($withBleed) / 25.4 * $dpi);
    }

    public static function heightPx(int $dpi = self::DPI, bool $withBleed = false): int
    {
        return (int) round(self::heightMm($withBleed) / 25.4 * $dpi);
    }

    /**
     * @return array{0: float, 1: float}
     */
    public static function paperPoints(bool $withBleed = false): array
    {
        return [
            self::widthMm($withBleed) * self::MM_TO_PT,
            self::heightMm($withBleed) * self::MM_TO_PT,
        ];
    }
}
