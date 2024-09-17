<?php

namespace PapaRascalDev\Sidekick\Drivers;

interface Driver
{
    public function complete();
    public function completeStreamed();
    public function getErrorMessage(array $response);
    public function getResponse(array $response);

}
