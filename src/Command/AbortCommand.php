<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Command;

use Devbanana\SpellingBeeHelper\Game;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'abort',
    description: 'Aborts the current game'
)]
final class AbortCommand extends Command
{
    use GameTrait;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (Game::getActiveGame() === null) {
            $io->error(self::NO_ACTIVE_GAME_ERROR);

            return Command::INVALID;
        }

        $game = Game::loadActiveGame();

        $game->abort();

        $io->success(
            sprintf('The game for %s has been aborted.', $game->getDate()->format('Y-m-d'))
        );

        return Command::SUCCESS;
    }
}
