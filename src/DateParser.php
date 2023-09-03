<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper;

final class DateParser
{
    public static function parse(string $dateStr): ?\DateTime
    {
        try {
            $date = \DateTime::createFromFormat('Y-m-d', $dateStr, new \DateTimeZone('UTC'));

            if ($date === false) {
                // Parsing failed
                return null;
            }

            // Set seconds to 00:00:00
            $date->setTime(0, 0, 0);

            return $date;
        } catch (\Exception $e) {
            // Handle any exceptions that might occur during parsing
            return null;
        }
    }
}
