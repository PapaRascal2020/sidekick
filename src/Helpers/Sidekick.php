<?php

use PapaRascalDev\Sidekick\SidekickManager;

if (! function_exists('sidekick')) {
    function sidekick(): SidekickManager
    {
        return app('sidekick');
    }
}
