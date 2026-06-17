<?php

namespace App\Support;

class WhatsApp
{
    public static function companyNumber(): ?string
    {
        return self::normalize(env('WHATSAPP_CONTACT', '6285128008030'));
    }

    public static function normalize(?string $value): ?string
    {
        $digits = PhoneNumber::digits($value);

        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    public static function link(?string $value, ?string $message = null): ?string
    {
        $number = self::normalize($value);

        if (! $number) {
            return null;
        }

        $query = filled($message) ? '?text='.rawurlencode((string) $message) : '';

        return 'https://wa.me/'.$number.$query;
    }

    public static function companyLink(?string $message = null): ?string
    {
        return self::link(self::companyNumber(), $message);
    }

    public static function customerLink(?string $value, ?string $message = null): ?string
    {
        return self::link($value, $message);
    }

    public static function display(?string $value): string
    {
        $number = self::normalize($value);

        if (! $number) {
            return '-';
        }

        if (! str_starts_with($number, '62')) {
            return '+'.$number;
        }

        $national = substr($number, 2);
        $chunks = str_split($national, 4);

        return '+62 '.implode('-', $chunks);
    }
}
