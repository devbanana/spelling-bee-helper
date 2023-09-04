<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Command;

use Devbanana\SpellingBeeHelper\Exception\WordAlreadyPlayedException;
use Devbanana\SpellingBeeHelper\Game;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('word', InputArgument::REQUIRED, 'The word to guess.')
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

        $word = (string) $input->getArgument('word');

        try {
            if ($game->guess($word)) {
                if ($game->isPangram($word)) {
                    $io->success('Pangram!');
                } else {
                    $io->success('That word was found in the puzzle.');
                }
            } else {
                $io->error('That word was not found in the puzzle.');
            }
        } catch (WordAlreadyPlayedException) {
            $io->error('That word was already played');

            return Command::INVALID;
        }

        self::showStatus($game, $io);

        return Command::SUCCESS;
    }
}
