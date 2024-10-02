<?php

use PapaRascalDev\Sidekick\Sidekick;
use PapaRascalDev\Sidekick\SidekickConversation;
use PapaRascalDev\Sidekick\SidekickDriverInterface;

if( !function_exists('sidekick') )
{
    /**
     * Creates a new instance of Sidekick
     *
     * @param SidekickDriverInterface $driver
     * @return SidekickDriverInterface
     */
    function sidekick(SidekickDriverInterface $driver): SidekickDriverInterface
    {
        return Sidekick::create($driver);
    }

}

if( !function_exists('sidekickConversation') )
{
    /**
     * Creates a new instance of SidekickConversation
     */
    function sidekickConversation(): SidekickConversation
    {
        return new SidekickConversation();
    }

}

if( !function_exists('sidekickUtilities') )
{
    /**
     * Creates a new instance of Sidekick
     *
     * @param SidekickDriverInterface $driver
     * @return mixed
     */
    function sidekickUtilities(SidekickDriverInterface $driver): mixed
    {
        $sidekick = Sidekick::create($driver);
        return $sidekick->utilities();
    }

}
