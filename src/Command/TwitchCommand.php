<?php

namespace App\Command;

use App\TwitchChatClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TwitchCommand extends Command
{
    protected static $defaultName = 'twitch';

    public function configure()
    {
        $this
            ->addOption('colors', 'c', InputOption::VALUE_OPTIONAL, 'Colored output or not', true)
            ->setDescription('Start the twitch chat bot.')
            ->setHelp('Connects to [TWITCH_CHANNEL] channel, with the bot account token [TWITCH_OAUTH]');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $colors = $input->getOption('colors');
        
        $client = new TwitchChatClient(getenv('TWITCH_CHANNEL'), getenv('TWITCH_USER'), getenv('TWITCH_OAUTH'));

        $client->connect();
        if (!$client->isConnected()) {
            if ($colors) {
                $output->writeln('<fg=#c0392b>It was not possible to connect.</>');
            } else {
                $output->writeln('It was not possible to connect.');
            }

            return Command::FAILURE;
        }

        if ($colors) {
            $output->writeln(sprintf('Successfully connected to <fg=green>%s</>!', getenv('TWITCH_CHANNEL')));
        } else {
            $output->writeln(sprintf('Successfully connected to %s!', getenv('TWITCH_CHANNEL')));
        }

        while (true) {
            $content = $client->read(512);

            //is it a ping?
            if (strstr($content, 'PING')) {
                $output->writeln(
                    'Received PING event',
                    OutputInterface::VERBOSITY_DEBUG
                );
                $output->writeln(
                    'Sending back PONG',
                    OutputInterface::VERBOSITY_DEBUG
                );
                $client->send('PONG :tmi.twitch.tv');
                continue;
            }
            //is it an actual msg?
            elseif (strstr($content, 'PRIVMSG')) {
                $output->writeln(
                    $content,
                    OutputInterface::VERBOSITY_VERBOSE
                );
                continue;
            }

            sleep(5);
        }
    }
}
