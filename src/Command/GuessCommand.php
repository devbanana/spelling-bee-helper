<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Command;

use Devbanana\SpellingBeeHelper\Exception\WordAlreadyPlayedException;
use Devbanana\SpellingBeeHelper\Game;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'guess',
    description: 'Guess a word from the Spelling Bee'
)]
final class GuessCommand extends Command
{
    use GameTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('word', InputArgument::OPTIONAL, 'The word to guess.')
            ->addOption('interactive', 'i', InputOption::VALUE_NONE, 'Guess in interactive mode')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (Game::getActiveGame() === null) {
            $io->error(self::NO_ACTIVE_GAME_ERROR);

            return Command::INVALID;
        }

        $game = Game::loadActiveGame();
        $interactive = $input->getOption('interactive');

        if ($interactive === false) {
            $word = $input->getArgument('word');

            if ($word === null) {
                $io->error('Please provide a word to guess.');

                return Command::INVALID;
            }
        }

        while (true) {
            if ($interactive === true) {
                $word = $io->ask('Enter a word to guess (or press Enter to exit)');

                if ($word === null) {
                    break;
                }
            }

            try {
                if ($game->guess($word)) {
                    $io->success($game->isPangram($word) ? 'Pangram!' : 'That word was found in the puzzle.');
                } else {
                    $io->error('That word was not found in the puzzle.');
                }
            } catch (WordAlreadyPlayedException) {
                $io->error('That word was already played');

                if ($interactive === true) {
                    continue;
                }

                return Command::INVALID;
            }

            self::showStatus($game, $io);

            if ($interactive === false) {
                break;
            }
        }

        return Command::SUCCESS;
    }
}
