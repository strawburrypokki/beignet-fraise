<?php

namespace App\Watchdog;

use RuntimeException;

class Message
{
    protected $nickname;
    protected $message;
    protected $rawMessage;

    public function __construct(string $rawMessage)
    {
        $this->rawMessage = $rawMessage;
    }

    /**
     * Parse $rawMessage to get $nickname and $message.
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function parseRawMessage()
    {
        if (preg_match('`.*:([a-zA-Z]+)!.*:(.*)`', $this->rawMessage, $matches)) {
            $this->setNickname($matches[1]);
            $this->setMessage($matches[2]);
        } else {
            throw new RuntimeException('Could not parse raw message "%s"', $this->rawMessage);
        }
    }

    /**
     * Get the value of nickname.
     */
    public function getNickname()
    {
        if (!$this->message) {
            $this->parseRawMessage();
        }

        return $this->nickname;
    }

    /**
     * Set the value of nickname.
     *
     * @return self
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get the value of message.
     */
    public function getMessage()
    {
        if (!$this->message) {
            $this->parseRawMessage();
        }

        return $this->message;
    }

    /**
     * Set the value of message.
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the value of rawMessage.
     */
    public function getRawMessage()
    {
        return $this->rawMessage;
    }

    /**
     * Set the value of rawMessage.
     *
     * @return self
     */
    public function setRawMessage($rawMessage)
    {
        $this->rawMessage = $rawMessage;

        return $this;
    }
}
