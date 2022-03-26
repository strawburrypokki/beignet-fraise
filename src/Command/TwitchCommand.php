<?php

namespace App\Command;

use App\TwitchChatClient;
use App\Watchdog\Watchdog;
use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class TwitchCommand extends Command
{
    protected static $defaultName = 'twitch';

    /**
     * Bot account name. Used for the PingBot subscriber.
     * @var string
     */
    protected $twitchBotAccount;

    /**
     * OAuth token to autorize with Twitch IRC chat.
     * @var string
     */
    protected $twitchOauth;

    /**
     * The twitch channel to join.
     * 
     * @var string
     */
    protected $twitchChannel;

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
        string $twitchChannel = null,
        string $name = null
    ) {
        parent::__construct($name);
        $this->twitchBotAccount = $twitchBotAccount;
        $this->twitchOauth = $twitchOauth;
        $this->twitchChannel = $twitchChannel;
        $this->watchdog = $watchdog;
    }

    public function configure()
    {
        $this
            ->addArgument('channel', InputArgument::OPTIONAL, 'The Twitch channel you wish to join. If not provided, command will look for the TWITCH_CHANNEL env variable.')
            ->addOption('colors', 'c', InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE, 'Colored output or not. (Default: true)')
            ->addOption('welcome', 'w', InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE, 'Bot says "hello" when joining the channel to notify successful connection. (Default: true)')
            ->setDescription('Start the Twitch chat bot.')
            ->setHelp('Connects to [TWITCH_CHANNEL] channel, with the bot account token [TWITCH_OAUTH]');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $colors = $input->getOption('colors');
        $channel = $input->getArgument('channel') ?? $this->twitchChannel;

        if (!strlen($channel)) {
            throw new RuntimeException('Missing "Channel" (got empty string)');
        }

        $logger = new ConsoleLogger($output);
        $client = new TwitchChatClient($channel, $this->twitchBotAccount, $this->twitchOauth);

        $client->connect($input->getOption('welcome'));
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
                $response = $this->watchdog->sniff($content, $logger);
                if(!$response) {
                    $output->writeln(
                        'I have nothing to say :|', 
                        OutputInterface::VERBOSITY_DEBUG
                    );
                }
                $client->say($response);
                continue;
            }
            sleep(3);
        }
    }
}
