<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Parses and renders the transaction number format configured in Settings,
 * for example "TRX-{YYYY}{MM}-{XXXX}".
 *
 * Tokens: {YYYY} {YY} year, {MM} month, {DD} day, {X...} zero-padded counter (exactly one).
 */
class TransactionNumberFormat
{
    public const DEFAULT_FORMAT = 'TRX-{YYYY}-{XXX}';

    public const DEFAULT_RESET = 'yearly';

    /** Reset periods from finest to coarsest. */
    public const PERIODS = ['daily', 'monthly', 'yearly', 'never'];

    private const COUNTER = '/\{(X+)\}/';

    public static function isValid(?string $format): bool
    {
        return is_string($format) && preg_match_all(self::COUNTER, $format) === 1;
    }

    /**
     * The finest reset period the format can show without producing duplicate numbers.
     * A day needs month and year, a month needs a year.
     */
    public static function finestPeriod(string $format): string
    {
        $hasYear = str_contains($format, '{YYYY}') || str_contains($format, '{YY}');
        $hasMonth = $hasYear && str_contains($format, '{MM}');
        $hasDay = $hasMonth && str_contains($format, '{DD}');

        return match (true) {
            $hasDay => 'daily',
            $hasMonth => 'monthly',
            $hasYear => 'yearly',
            default => 'never',
        };
    }

    public static function supportsReset(string $format, string $reset): bool
    {
        return self::rank($reset) >= self::rank(self::finestPeriod($format));
    }

    /**
     * The reset period actually used: the configured one, made coarser when the format
     * lacks the date parts needed to keep numbers unique.
     */
    public static function effectivePeriod(string $format, string $reset): string
    {
        if (! in_array($reset, self::PERIODS, true)) {
            $reset = self::DEFAULT_RESET;
        }

        return self::supportsReset($format, $reset) ? $reset : self::finestPeriod($format);
    }

    public static function periodKey(string $period, CarbonInterface $date): string
    {
        return match ($period) {
            'daily' => $date->format('Ymd'),
            'monthly' => $date->format('Ym'),
            'yearly' => $date->format('Y'),
            default => 'all',
        };
    }

    public static function render(string $format, CarbonInterface $date, int $number): string
    {
        $withDates = self::replaceDates($format, $date);

        return preg_replace_callback(
            self::COUNTER,
            fn (array $m) => str_pad((string) $number, strlen($m[1]), '0', STR_PAD_LEFT),
            $withDates
        );
    }

    /**
     * Text before the counter, used to narrow the search for numbers already issued.
     */
    public static function prefix(string $format, CarbonInterface $date): string
    {
        return preg_split(self::COUNTER, self::replaceDates($format, $date), 2)[0];
    }

    /**
     * Regex that matches numbers issued with this format on this date and captures the counter.
     */
    public static function pattern(string $format, CarbonInterface $date): string
    {
        [$before, $after] = preg_split(self::COUNTER, self::replaceDates($format, $date), 2);

        return '/^' . preg_quote($before, '/') . '(\d+)' . preg_quote($after, '/') . '$/';
    }

    private static function replaceDates(string $format, CarbonInterface $date): string
    {
        return strtr($format, [
            '{YYYY}' => $date->format('Y'),
            '{YY}' => $date->format('y'),
            '{MM}' => $date->format('m'),
            '{DD}' => $date->format('d'),
        ]);
    }

    private static function rank(string $period): int
    {
        $index = array_search($period, self::PERIODS, true);

        return $index === false ? array_search(self::DEFAULT_RESET, self::PERIODS, true) : $index;
    }
}
