<?php

namespace App\Twitch;

use RuntimeException;

class Message
{
    /**
     * @var boolean
     */
    private $parsed = false;

    /**
     * @var string
     */
    protected $nickname;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $rawMessage;

    /**
     * @var string
     */
    protected $rawTags;

    /**
     * Is message a exclamation mark command (!command)?
     * 
     * @var bool
     */
    protected $isCommand = false;

    /**
     * Is the author of the message the broadcaster of the channel?
     * 
     * @var bool
     */
    protected $isBroadcaster = false;

    /**
     * Is the author of the message a moderator of the channel?
     * 
     * @var bool
     */
    protected $isModerator = false;

    public function __construct(string $rawMessage)
    {
        $this->rawMessage = $rawMessage;
    }

    /**
     * Parse $rawMessage to get $nickname and $message.
     *
     * @return self
     *
     * @throws RuntimeException
     */
    public function parseRawMessage()
    {
        if($this->parsed) {
            return;
        }

        // Regex follows this pattern
        // (badges):(author)!<random shit IDGAF> PRIVMSG #<channel name> :(the actual message body)
        if (preg_match('`(.*):([a-zA-Z]+)!.*PRIVMSG \#[a-zA-Z]+ :(.*)`', $this->rawMessage, $matches)) {
            $this->parsed = true;
            $this->setRawTags($matches[1]);
            $this->setNickname($matches[2]);
            $this->setMessage($matches[3]);

            // TODO: which is quicker / lighter between "mod=0" or search for "badge=moderator"
            // What if mod if also sub? What does the badges looks like
            if (strstr($this->getRawTags(), Badge::BADGE_MODERATOR)) {
                $this->setIsModerator(true);
            } elseif (strstr($this->getRawTags(), Badge::BADGE_BROADCASTER)) {
                $this->setIsBroadcaster(true);
            }

            if(0 === strpos($this->getMessage(), '!')) {
                $this->setIsCommand(true);
            }
        } else {
            throw new RuntimeException('Could not parse raw message "%s"', $this->rawMessage);
        }

        return $this;
    }

    public function hasRankModerator(): bool
    {
        return $this->isModerator() || $this->isBroadcaster();
    }

    public function getNickname(): ?string
    {
        if (!$this->nickname) {
            $this->parseRawMessage();
        }

        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getMessage(): ?string
    {
        if (!$this->message) {
            $this->parseRawMessage();
        }

        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getRawMessage(): string
    {
        return $this->rawMessage;
    }

    public function setRawMessage(string $rawMessage): self
    {
        $this->rawMessage = $rawMessage;

        return $this;
    }

    public function isBroadcaster(): bool
    {
        return $this->isBroadcaster;
    }

    public function setIsBroadcaster(bool $isBroadcaster): self
    {
        $this->isBroadcaster = $isBroadcaster;

        return $this;
    }

    public function isModerator(): bool
    {
        return $this->isModerator;
    }

    public function setIsModerator(bool $isModerator): self
    {
        $this->isModerator = $isModerator;

        return $this;
    }

    public function isCommand(): bool
    {
        return $this->isCommand;
    }

    public function setIsCommand(bool $isCommand): self
    {
        $this->isCommand = $isCommand;

        return $this;
    }

    public function getRawTags(): ?string
    {
        return $this->rawTags;
    }

    public function setRawTags(string $rawTags): self
    {
        $this->rawTags = $rawTags;

        return $this;
    }
}
