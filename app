#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use App\Command\TwitchCommand;
use App\TwitchChatClient;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->usePutenv(true);
// loads .env, .env.local, and .env.$APP_ENV.local or .env.$APP_ENV
$dotenv->loadEnv(__DIR__.'/.env');

$application = new Application();
$application->add(new TwitchCommand());
$application->run();
