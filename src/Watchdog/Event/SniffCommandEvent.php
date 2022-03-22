<?php

namespace App\Watchdog\Event;

class SniffCommandEvent extends AbstractTwitchMessageEvent
{
    public const NAME = 'command.process';
}
