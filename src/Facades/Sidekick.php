<?php

namespace PapaRascalDev\Sidekick\Facades;

use Illuminate\Support\Facades\Facade;

class Sidekick extends Facade
{

    /**
     * @see \PapaRascalDev\Sidekick\Sidekick
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sidekick';
    }
}
