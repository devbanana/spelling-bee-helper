<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Exception;

final class NoMoreHistoricalWordsException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('No more historical words are available.');
    }
}
