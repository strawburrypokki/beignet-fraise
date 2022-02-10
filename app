#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use App\Command\TwitchCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dotenv = new Dotenv();
$dotenv->usePutenv(true);
// loads .env, .env.local, and .env.$APP_ENV.local or .env.$APP_ENV
$dotenv->loadEnv(__DIR__.'/.env');

$container = new ContainerBuilder();
$container->register(EventDispatcher::class, EventDispatcher::class);
$container->addCompilerPass(new RegisterListenersPass(EventDispatcher::class));

$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/config'));
$loader->load('services.yaml');
$container->compile();

$commandLoader = new ContainerCommandLoader($container, [
    'twitch' => TwitchCommand::class
]);

$application = new Application('TwitchBot');
$application->setCommandLoader($commandLoader);
$application->run();
