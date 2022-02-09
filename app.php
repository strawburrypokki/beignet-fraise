#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use App\TwitchChatClient;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->usePutenv(true);
// loads .env, .env.local, and .env.$APP_ENV.local or .env.$APP_ENV
$dotenv->loadEnv(__DIR__.'/.env');


$client = new TwitchChatClient(getenv('TWITCH_CHANNEL'), getenv('TWITCH_OAUTH'), 'BeignetFraise');

$client->connect();
if (!$client->isConnected()) {
    $this->getPrinter()->error("It was not possible to connect.");
    return;
}
dump('Connected.');
while (true) {
    $content = $client->read(512);

    //is it a ping?
    if (strstr($content, 'PING')) {
        $client->send('PONG :tmi.twitch.tv');
        continue;
    }
    //is it an actual msg?
    elseif (strstr($content, 'PRIVMSG')) {
        dump($content);
        continue;
    }

    sleep(5);
}

// $application = new Application();

// ... register commands

// $application->run();