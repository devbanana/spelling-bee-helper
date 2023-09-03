<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper\Command;

use Devbanana\SpellingBeeHelper\DateParser;
use Devbanana\SpellingBeeHelper\PuzzleDownloader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'download',
    description: 'Downloads Spelling Bee solutions for a given date or period.'
)]
final class DownloadCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'date',
                InputArgument::OPTIONAL,
                'The date for which to download the solution'
            )
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'The start date of the range')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'The end date of the range')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Download Puzzles');

        if ($input->getArgument('date') === null) {
            $start = $input->getOption('start');
            $end = $input->getOption('end');

            if ($start === null && $end === null) {
                $io->error(
                    'Either date must be provided, or both start and end dates must be specified.'
                );

                return Command::INVALID;
            }

            $start = DateParser::parse($start);
            $end = DateParser::parse($end);

            if ($start === null || $end === null) {
                $io->error('Invalid date specified.');

                return Command::INVALID;
            }

            if ($end < $start) {
                $io->error('The start date must be before end date.');

                return Command::INVALID;
            }
        } else {
            $start = DateParser::parse($input->getArgument('date'));

            if ($start === null) {
                $io->error('Invalid date specified.');

                return Command::INVALID;
            }

            $end = clone $start;
        }

        $downloader = new PuzzleDownloader();

        while ($end >= $start) {
            if (!$downloader->exists($end)) {
                $io->text($end->format('Y-m-d') . '...');
                $words = $downloader->download($end);
                $io->success(sprintf('Downloaded %d words', \count($words)));

                // Sleep so we don't overload the server
                usleep(random_int(500000, 5000000));
            }

            $end->sub(new \DateInterval('P1D'));
        }

        return Command::SUCCESS;
    }
}
