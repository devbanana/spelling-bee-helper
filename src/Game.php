<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper;

use Devbanana\SpellingBeeHelper\Exception\WordAlreadyPlayedException;

final class Game
{
    private const GAMEs_DIR = 'var/data/games';

    private \DateTimeInterface $date;

    /**
     * @var string[]
     */
    private array $words;

    /**
     * @var string[]
     */
    private array $historicalWords;

    /**
     * @var string[]
     */
    private array $letters = [];

    /**
     * @var string[]
     */
    private array $wordsFound = [];

    /**
     * @var int|null Cached total points
     */
    private ?int $totalPoints = null;

    /**
     * @var int|null Cached total words
     */
    private ?int $totalWords = null;

    /**
     * @var int|null Cached total pangrams
     */
    private ?int $totalPangrams = null;

    public function __construct(?\DateTimeInterface $date = null)
    {
        if ($date === null) {
            $date = DateParser::getToday();
        }

        $this->date = $date;

        $downloader = new PuzzleDownloader();
        $this->words = $downloader->download($date);
        $this->historicalWords = $downloader->getWordListExcept($date);

        if (file_exists($this->getGamePath($date))) {
            $lines = file($this->getGamePath($date), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $this->letters = str_split(array_shift($lines));
            $this->wordsFound = $lines;
        } else {
            if (!file_exists(self::GAMEs_DIR)) {
                mkdir(self::GAMEs_DIR, 0o755, true);
            }

            file_put_contents($this->getGamePath($date), implode('', $this->getLetters()) . "\n");
        }
    }

    public static function getActiveGame(): ?\DateTimeInterface
    {
        $activeGamePath = self::GAMEs_DIR . '/active-game.txt';

        if (!file_exists($activeGamePath)) {
            return null;
        }

        $date = DateParser::parse(trim(file_get_contents($activeGamePath)));
        if ($date === null) {
            throw new \RuntimeException('Date of active game is an invalid date.');
        }

        return $date;
    }

    public static function loadActiveGame()
    {
        $activeGame = self::getActiveGame();

        if ($activeGame === null) {
            throw new \RuntimeException('There are currently no active games.');
        }

        return new self($activeGame);
    }

    public function start(): void
    {
        if (self::getActiveGame() !== null) {
            throw new \RuntimeException('Another game is already active.');
        }

        file_put_contents(
            self::GAMEs_DIR . '/active-game.txt',
            $this->date->format('Y-m-d')
        );
    }

    /**
     * @return string[] The list of acceptable letters in this puzzle
     */
    public function getLetters(): array
    {
        if (!empty($this->letters)) {
            return $this->letters;
        }

        $letters = array_unique(str_split(implode('', $this->words)));
        shuffle($letters);

        // Find the common letter present in all words
        $centerLetter = $this->findCenterLetter($letters);

        // If a common letter is found, move it to the beginning of the array
        $centerLetterIndex = array_search($centerLetter, $letters, true);

        if ($centerLetterIndex !== false) {
            unset($letters[$centerLetterIndex]);
            array_unshift($letters, $centerLetter);
        }

        return $this->letters = $letters;
    }

    public function getRank(): string
    {
        $percent = (float) $this->getPoints() / $this->getTotalPoints();

        if ($percent === 1.0) {
            return 'Queen Bee';
        }
        if ($percent >= 0.7) {
            return 'Genius';
        }
        if ($percent >= 0.5) {
            return 'Amazing';
        }
        if ($percent >= 0.4) {
            return 'Great';
        }
        if ($percent >= 0.25) {
            return 'Nice';
        }
        if ($percent >= 0.15) {
            return 'Solid';
        }
        if ($percent >= 0.08) {
            return 'Good';
        }
        if ($percent >= 0.05) {
            return 'Moving Up';
        }
        if ($percent >= 0.02) {
            return 'Good Start';
        }

        return 'Beginner';
    }

    public function getTotalPoints(): int
    {
        if ($this->totalPoints === null) {
            $this->totalPoints = 0;

            foreach ($this->words as $word) {
                $this->totalPoints += $this->getPointsForWord($word);
            }
        }

        return $this->totalPoints;
    }

    public function getPoints(): int
    {
        $points = 0;

        foreach ($this->wordsFound as $word) {
            $points += $this->getPointsForWord($word);
        }

        return $points;
    }

    public function getTotalWords(): int
    {
        if ($this->totalWords === null) {
            $this->totalWords = \count($this->words);
        }

        return $this->totalWords;
    }

    public function getWords(): int
    {
        return \count($this->wordsFound);
    }

    public function getTotalPangrams(): int
    {
        if ($this->totalPangrams === null) {
            $this->totalPangrams = 0;

            foreach ($this->words as $word) {
                if ($this->isPangram($word)) {
                    ++$this->totalPangrams;
                }
            }
        }

        return $this->totalPangrams;
    }

    public function getPangrams(): int
    {
        $pangrams = 0;

        foreach ($this->wordsFound as $word) {
            if ($this->isPangram($word)) {
                ++$pangrams;
            }
        }

        return $pangrams;
    }

    public function isPangram(string $word): bool
    {
        $uniqueLetters = array_unique(str_split($word));

        return \count($uniqueLetters) === 7;
    }

    public function guess(string $word): bool
    {
        $word = strtolower(trim($word));

        if (\in_array($word, $this->wordsFound, true)) {
            throw new WordAlreadyPlayedException();
        }

        if (!\in_array($word, $this->words, true)) {
            return false;
        }

        $this->wordsFound[] = $word;

        $this->saveState();

        return true;
    }

    public function getHistoricalWord(): ?string
    {
        $historicalWords = $this->historicalWords;
        shuffle($historicalWords);

        foreach ($historicalWords as $word) {
            if (\in_array($word, $this->words, true) && !\in_array($word, $this->wordsFound, true)) {
                $this->guess($word);

                return $word;
            }
        }

        return null;
    }

    private function getGamePath(\DateTimeInterface $date): string
    {
        return sprintf('%s/game_%s.txt', self::GAMEs_DIR, $date->format('Y-m-d'));
    }

    /**
     * @param string[] $letters The unique letters in this puzzle
     *
     * @return string The letter that is in common between all words
     */
    private function findCenterLetter(array $letters): string
    {
        $commonLetters = $letters;

        foreach ($this->words as $word) {
            $commonLetters = array_intersect($commonLetters, str_split($word));

            if (empty($commonLetters)) {
                throw new \RuntimeException('No letters are in common in the word list.');
            }
        }

        if (\count($commonLetters) > 1) {
            throw new \RuntimeException('More than one letter was found in common in the word list.');
        }

        return reset($commonLetters);
    }

    private function getPointsForWord(string $word): int
    {
        $wordLength = \strlen($word);

        if ($wordLength === 4) {
            return 1;
        }

        $points = $wordLength;

        if ($this->isPangram($word)) {
            $points += 7;
        }

        return $points;
    }

    private function saveState(): void
    {
        $lines = [implode('', $this->getLetters())];
        $lines = array_merge($lines, $this->wordsFound);

        file_put_contents($this->getGamePath($this->date), implode("\n", $lines) . "\n");
    }
}
