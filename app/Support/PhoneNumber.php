<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PhoneNumber
{
    public static function normalize(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0'.$digits;
        }

        return $digits;
    }

    public static function digits(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        return $digits !== '' ? $digits : null;
    }

    public static function candidates(?string $value): array
    {
        $normalized = self::normalize($value);
        $digits = self::digits($value);

        if (! $normalized && ! $digits) {
            return [];
        }

        $candidates = array_filter([
            $digits,
            $normalized ? self::digits($normalized) : null,
        ]);

        $localDigits = $normalized ? self::digits($normalized) : null;
        if ($localDigits && str_starts_with($localDigits, '0')) {
            $nationalDigits = substr($localDigits, 1);

            if ($nationalDigits !== '') {
                $candidates[] = $nationalDigits;
                $candidates[] = '62'.$nationalDigits;
            }
        }

        return array_values(array_unique($candidates));
    }

    public static function applyMatchQuery(Builder $query, string $column, ?string $value): Builder
    {
        $candidates = self::candidates($value);

        if (empty($candidates)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn(DB::raw(self::cleanedColumnExpression($column)), $candidates);
    }

    private static function cleanedColumnExpression(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(COALESCE({$column}, '')), ' ', ''), '-', ''), '.', ''), '(', ''), ')', ''), '+', ''), '/', '')";
    }
}
