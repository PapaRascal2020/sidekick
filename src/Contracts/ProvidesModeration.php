<?php

namespace PapaRascalDev\Sidekick\Contracts;

use PapaRascalDev\Sidekick\Responses\ModerationResponse;

interface ProvidesModeration
{
    public function moderate(string $model, string $content): ModerationResponse;
}
