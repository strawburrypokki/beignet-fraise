<?php

namespace App\Watchdog\Event;

class SniffMessageEvent extends AbstractTwitchMessageEvent
{
    public const NAME = 'message.process';
}
