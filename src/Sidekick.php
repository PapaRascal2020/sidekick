<?php

namespace PapaRascalDev\Sidekick;

/**
 * Sidekick
 *
 * Loads and returns the specified driver
 */

class Sidekick
{

    /**
     * @param SidekickDriverInterface $sidekickDriver
     * @return SidekickDriverInterface
     */
    public static function create(SidekickDriverInterface $sidekickDriver): SidekickDriverInterface
    {
        return new $sidekickDriver();
    }

}
