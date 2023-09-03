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
    name: 'letters',
    description: 'Show the valid letters from the current game'
)]
final class LettersCommand extends Command
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

        $letters = $game->getLetters();

        $io->text(strtoupper(implode(' ', $letters)));

        return Command::SUCCESS;
    }
}
