<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Command;

use Devbanana\SpellingBeeHelper\Exception\NoHistoricalWordsMatchFilters;
use Devbanana\SpellingBeeHelper\Exception\NoMoreHistoricalWordsException;
use Devbanana\SpellingBeeHelper\Game;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'historical-word',
    description: 'Reveals a historical word that matches the allowed letters'
)]
final class HistoricalWordCommand extends Command
{
    use GameTrait;

    protected function configure(): void
    {
        $this
            ->addOption('length', 'l', InputOption::VALUE_REQUIRED, 'Filter words by length')
            ->addOption('starts-with', 's', InputOption::VALUE_REQUIRED, 'Filter words that start with a specific string')
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

        $filters = [];

        $lengthFilter = $input->getOption('length');
        if ($lengthFilter !== null) {
            $filters[] = static fn (string $word): bool => \strlen($word) === (int) $lengthFilter;
        }

        $startsWithFilter = $input->getOption('starts-with');
        if ($startsWithFilter !== null) {
            $filters[] = static fn (string $word): bool => str_starts_with($word, $startsWithFilter);
        }

        try {
            $word = $game->getHistoricalWord($filters);

            $io->success('Found word: ' . $word);

            self::showStatus($game, $io);
        } catch (NoMoreHistoricalWordsException) {
            $io->error('There are no more historical words that match the allowed letters.');

            return Command::FAILURE;
        } catch (NoHistoricalWordsMatchFilters) {
            $io->error('No historical words match the provided filters.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
