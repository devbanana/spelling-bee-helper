<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Command;

use Devbanana\SpellingBeeHelper\Game;
use Symfony\Component\Console\Style\SymfonyStyle;

trait GameTrait
{
    private static function showStatus(Game $game, SymfonyStyle $io): void
    {
        $io->definitionList(
            ['Rank' => $game->getRank()],
            ['Points' => $game->getPoints() . '/' . $game->getTotalPoints()],
            ['Words' => $game->getWords() . '/' . $game->getTotalWords()],
            ['Pangrams' => $game->getPangrams() . '/' . $game->getTotalPangrams()]
        );
    }
}
