<?php

namespace App\Support;

/**
 * Thermal paper sizes for the receipt and the width a printer can actually print on each.
 */
class ReceiptPaper
{
    public const DEFAULT_SIZE = '80mm';

    /** Printable width in mm. Thermal heads leave a few millimetres unprinted at the edges. */
    private const PRINTABLE_WIDTH = [
        '58mm' => 52,
        '80mm' => 70,
    ];

    /** Narrowest text column, in mm, that still fits a price next to an item line. */
    private const MIN_CONTENT_WIDTH = 32;

    public static function sizes(): array
    {
        return array_keys(self::PRINTABLE_WIDTH);
    }

    public static function normalize(?string $size): string
    {
        return array_key_exists((string) $size, self::PRINTABLE_WIDTH) ? $size : self::DEFAULT_SIZE;
    }

    public static function printableWidth(?string $size): int
    {
        return self::PRINTABLE_WIDTH[self::normalize($size)];
    }

    /** Largest left and right margin, in mm, that keeps the text column readable. */
    public static function maxMargin(?string $size): int
    {
        return intdiv(self::printableWidth($size) - self::MIN_CONTENT_WIDTH, 2);
    }
}
