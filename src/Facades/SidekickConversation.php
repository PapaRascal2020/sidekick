<?php

namespace PapaRascalDev\Sidekick\Facades;

use Illuminate\Support\Facades\Facade;

class SidekickConversation extends Facade
{

    /**
     * @see \PapaRascalDev\Sidekick\SidekickConversation
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sidekickConversion';
    }
}
