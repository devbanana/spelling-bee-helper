<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Exception;

final class NoHistoricalWordsMatchFilters extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('No historical words match the provided filters.');
    }
}
