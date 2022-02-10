<?php

namespace App;

class TwitchChatClient
{
    protected $socket;
    protected $channel;
    protected $oauth;
    protected $nick;

    public static $host = 'irc.chat.twitch.tv';
    public static $port = '6667';
    public static $pong_response = ':tmi.twitch.tv';

    /**
     * @param string $channel The twitch channel you wish to join
     * @param string $nick    The user account to login as
     * @param string $oauth   The OAuth token generated for the $nick account
     */
    public function __construct($channel, $nick, $oauth)
    {
        $this->channel = $channel;
        $this->oauth = $oauth;
        $this->nick = $nick;
    }

    /**
     * Connect to the configure twitch channel.
     *
     * @param bool   $sayHello Wether to say hello or not
     * @param string $color    nick color (Blue, Coral, DodgerBlue, SpringGreen, YellowGreen, Green, OrangeRed, Red, GoldenRod, HotPink, CadetBlue, SeaGreen, Chocolate, BlueViolet, Firebrick)
     *
     * @return void
     */
    public function connect($sayHello = true, $color = 'HotPink')
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (false === socket_connect($this->socket, self::$host, self::$port)) {
            return null;
        }

        $this->authenticate();
        $this->setNick();
        $this->joinChannel($this->channel);

        // Change color 💅
        // Blue, Coral, DodgerBlue, SpringGreen, YellowGreen, Green, OrangeRed, Red, GoldenRod, HotPink, CadetBlue, SeaGreen, Chocolate, BlueViolet, and Firebrick.
        $this->say('/color '.$color);
        // Welcome message
        if ($sayHello) {
            $this->say('/me *beep boop beep* MrDestructoid');
        }
    }

    public function authenticate()
    {
        $this->send(sprintf('PASS %s', $this->oauth));
    }

    public function setNick()
    {
        $this->send(sprintf('NICK %s', $this->nick));
    }

    public function joinChannel($channel)
    {
        $this->send(sprintf('JOIN #%s', $channel));
    }

    public function say($message)
    {
        $this->send(sprintf('PRIVMSG #%s :%s', $this->channel, $message));
    }

    public function getLastError()
    {
        return socket_last_error($this->socket);
    }

    public function isConnected()
    {
        return !is_null($this->socket);
    }

    public function read($size = 256)
    {
        if (!$this->isConnected()) {
            return null;
        }

        return socket_read($this->socket, $size);
    }

    public function send($message)
    {
        if (!$this->isConnected()) {
            return null;
        }

        return socket_write($this->socket, $message."\r\n");
    }

    public function close()
    {
        socket_close($this->socket);
    }

    public function __destruct()
    {
        $this->close();
    }
}
