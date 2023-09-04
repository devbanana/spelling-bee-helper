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
    name: 'historical-word',
    description: 'Reveals a historical word that matches the allowed letters'
)]
final class HistoricalWordCommand extends Command
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

        $word = $game->getHistoricalWord();

        if ($word === null) {
            $io->error('There are no more historical words that match the allowed letters.');

            return Command::FAILURE;
        }

        $io->success('Found word: ' . $word);

        self::showStatus($game, $io);

        return Command::SUCCESS;
    }
}
