<?php

namespace App\Watchdog\Subscriber\Command;

abstract class AbstractCommandSubscriber
{
    /**
     * Get the command name following the format [!commandname]
     *
     * @return string
     */
    abstract public function getCommandName();

    public function supports(string $message)
    {
        // The trailling space is important because commands follows the following pattern
        // !commandname action arguments
        return 0 === strpos($message, '!' . trim($this->getCommandName()) . ' ');
    }
}
