#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Devbanana\SpellingBeeHelper\Command\DownloadCommand;
use Devbanana\SpellingBeeHelper\Command\GuessCommand;
use Devbanana\SpellingBeeHelper\Command\StartCommand;
use Symfony\Component\Console\Application;

$application = new Application('Spelling Bee Helper');

$application->add(new DownloadCommand());
$application->add(new StartCommand());
$application->add(new GuessCommand());

$application->run();
