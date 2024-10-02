<?php

namespace PapaRascalDev\Sidekick\Utilities;

/****************************************************************
 *                                                              *
 *  OpenAi Helper                                               *
 *                                                              *
 *  This is a set of helper functions for people using          *
 *  OpenAi models                                               *
 *                                                              *
 ****************************************************************/

class OpenAiExtras extends Utilities
{
    /**
     * @param string $content
     * @param string $model
     * @param array $exclusions
     * @return bool
     */
    public function isContentFlagged (
        string $content,
        string $model = 'text-moderation-latest',
        array  $exclusions = []) : bool
    {
        $response = $this->sidekick->moderate()
            ->text( model: $model,
                    content: $content);

        if(!isset($response['results']['categories'])) return false;

        foreach($response['results']['categories'] as $category => $bool) {
            if (!in_array($category, $exclusions) && $bool) return true;
        }

        return false;
    }
}
