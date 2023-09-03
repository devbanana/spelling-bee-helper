<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Command;

use Devbanana\SpellingBeeHelper\Game;
use Symfony\Component\Console\Style\SymfonyStyle;

trait GameTrait
{
    private const NO_ACTIVE_GAME_ERROR = 'No game is currently active. Please start a game first.';

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
