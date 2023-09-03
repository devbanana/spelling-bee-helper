<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Command;

use Devbanana\SpellingBeeHelper\DateParser;
use Devbanana\SpellingBeeHelper\Game;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'start',
    description: 'Start a new game'
)]
final class StartCommand extends Command
{
    use GameTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('date', InputArgument::OPTIONAL, 'The date of the game to start')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = $input->getArgument('date');
        if ($date === null) {
            $date = DateParser::getToday();
        } else {
            $date = DateParser::parse($date);
        }

        $active = Game::getActiveGame();
        if ($active !== null) {
            if ($active->format('Y-m-d') === $date->format('Y-m-d')) {
                $io->error('This game is already started.');
            } else {
                $io->error(sprintf(
                    'The game for %s is already active, so a new game cannot be started.',
                    $active->format('Y-m-d')
                ));
            }

            return Command::INVALID;
        }

        $io->title($date->format('F j, Y'));

        $game = new Game($date);
        $game->start();

        $letters = $game->getLetters();

        $io->text(strtoupper(implode(' ', $letters)));
        $io->newLine();

        self::showStatus($game, $io);

        return Command::SUCCESS;
    }
}
