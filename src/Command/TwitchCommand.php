<?php

namespace App\Command;

use App\TwitchChatClient;
use App\Watchdog\Watchdog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TwitchCommand extends Command
{
    protected static $defaultName = 'twitch';

    /**
     * @var string
     */
    protected $twitchBotAccount;
    /**
     * @var string
     */
    protected $twitchOauth;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @param Watchdog $watchdog
     * @param string|null $name
     */
    public function __construct(
        string $twitchBotAccount, 
        string $twitchOauth,
        Watchdog $watchdog,
        string $name = null)
    {
        parent::__construct($name);
        $this->twitchBotAccount = $twitchBotAccount;
        $this->twitchOauth = $twitchOauth;
        $this->watchdog = $watchdog;
    }

    public function configure()
    {
        $this
            ->addArgument('channel', InputArgument::REQUIRED, 'The Twitch channel you wish to join')
            ->addOption('colors', 'c', InputOption::VALUE_OPTIONAL, 'Colored output or not', true)
            ->addOption('say-hello', 'w', InputOption::VALUE_OPTIONAL, 'Say a "hello" when joining the channel', true)
            ->setDescription('Start the Twitch chat bot.')
            ->setHelp('Connects to [TWITCH_CHANNEL] channel, with the bot account token [TWITCH_OAUTH]');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $colors = $input->getOption('colors');
        $channel = $input->getArgument('channel');

        $client = new TwitchChatClient($channel, $this->twitchBotAccount, $this->twitchOauth);

        $client->connect($input->getOption('say-hello'));
        if (!$client->isConnected()) {
            if ($colors) {
                $output->writeln('<error>It was not possible to connect.</>');
            } else {
                $output->writeln('It was not possible to connect.');
            }

            return Command::FAILURE;
        }

        if ($colors) {
            $output->writeln(sprintf('Successfully connected to <info>%s</>!', $channel));
        } else {
            $output->writeln(sprintf('Successfully connected to %s!', $channel));
        }

        while (true) {
            $content = $client->read(512);
            $output->writeln(
                $content,
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

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
                $response = $this->watchdog->sniff($content);
                $client->say($response);
                continue;
            }
            sleep(3);
        }
    }
}
