<?php

namespace PapaRascalDev\Sidekick;

use PapaRascalDev\Sidekick\Drivers\Driver;


class Sidekick
{
    protected Driver $driver;

    /**
     * @param Driver $driver
     * @return Driver
     */
    public static function create(Driver $driver): Driver
    {
        return new $driver();
    }

}
