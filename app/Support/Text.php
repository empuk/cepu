<?php

namespace App\Support;

final class Text
{
    /**
     * Strip tags, collapse whitespace, trim, and hard-limit user input.
     */
    public static function clean(?string $value, int $max): string
    {
        $clean = strip_tags((string) $value);
        $clean = preg_replace('/[\s\x{00A0}]+/u', ' ', $clean) ?? '';
        $clean = trim($clean);

        return mb_substr($clean, 0, $max);
    }
}
