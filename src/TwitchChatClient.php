<?php

namespace App;

class TwitchChatClient
{
    protected $socket;
    protected $channel;
    protected $oauth;
    protected $nick;

    static $host = "irc.chat.twitch.tv";
    static $port = "6667";
    static $pong_response = ":tmi.twitch.tv";

    public function __construct($channel, $oauth, $nick)
    {
        $this->channel = $channel;
        $this->oauth = $oauth;
        $this->nick = $nick;
    }

    public function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (socket_connect($this->socket, self::$host, self::$port) === FALSE) {
            return null;
        }

        $this->authenticate();
        $this->setNick();
        $this->joinChannel($this->channel);

        // Welcom message
        $this->say('/me *beep boop beep* MrDestructoid');
    }

    public function authenticate()
    {
        $this->send(sprintf("PASS %s", $this->oauth));
    }

    public function setNick()
    {
        $this->send(sprintf("NICK %s", $this->nick));
    }

    public function joinChannel($channel)
    {
        $this->send(sprintf("JOIN #%s", $channel));
    }
    
    public function say($message)
    {
        $this->send(sprintf("PRIVMSG #%s :%s", $this->channel, $message));
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

        return socket_write($this->socket, $message . "\r\n");
    }

    public function close()
    {
        socket_close($this->socket);
    }

    public function __destruct() {
        $this->close();
    }
}