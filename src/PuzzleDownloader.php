<?php

declare(strict_types=1);

namespace Devbanana\SpellingBeeHelper;

final class PuzzleDownloader
{
    private const PUZZLES_DIR = 'var/data/puzzles';

    public function __construct()
    {
        if (!file_exists(self::PUZZLES_DIR)) {
            mkdir(self::PUZZLES_DIR, 0o755, true);
        }
    }

    /**
     * @return string[] the list of words
     */
    public function download(\DateTimeInterface $date): array
    {
        if ($this->exists($date)) {
            return self::getWords(self::getPuzzlePath($date));
        }

        $url = sprintf('https://nytbee.com/Bee_%s.html', $date->format('Ymd'));

        $html = file_get_contents($url);
        if ($html === false) {
            throw new \RuntimeException(
                'Could not download the puzzle for ' . $date->format('Y-m-d')
            );
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $answersDiv = $dom->getElementById('main-answer-list');
        if ($answersDiv === null) {
            throw new \RuntimeException('Could not find the list of words.');
        }

        $wordElements = $answersDiv->getElementsByTagName('li');

        $words = [];

        /** @var \DOMNode $wordElement */
        foreach ($wordElements as $wordElement) {
            $words[] = trim(strtolower($wordElement->textContent));
        }

        file_put_contents(
            self::getPuzzlePath($date),
            implode("\n", $words) . "\n"
        );

        return $words;
    }

    public function exists(\DateTimeInterface $date): bool
    {
        return file_exists(self::getPuzzlePath($date));
    }

    public function getWordListExcept(\DateTimeInterface $date)
    {
        $files = glob(self::PUZZLES_DIR . '/*.txt');
        $words = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_FILENAME) === $date->format('Y-m-d')) {
                continue;
            }

            $words = array_merge($words, self::getWords($file));
        }

        sort($words);

        return array_unique($words);
    }

    private static function getPuzzlePath(\DateTimeInterface $date): string
    {
        return sprintf('%s/%s.txt', self::PUZZLES_DIR, $date->format('Y-m-d'));
    }

    private static function getWords(string $path): array|false
    {
        return file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
