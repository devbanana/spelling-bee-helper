<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Exception;

final class WordAlreadyPlayedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('That word was already played.');
    }
}
