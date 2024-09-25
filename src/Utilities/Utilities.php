<?php

namespace PapaRascalDev\Sidekick\Utilities;

use PapaRascalDev\Sidekick\Sidekick;
use PapaRascalDev\Sidekick\SidekickDriverInterface;

/****************************************************************
 *                                                              *
 *  Claude Helper                                              *
 *                                                              *
 *  This is a set of helper functions for people using          *
 *  Claude models                                              *
 *                                                              *
 ****************************************************************/

class Utilities
{
    protected $sidekick;
    public function __construct(SidekickDriverInterface $driver) {
        $this->sidekick = Sidekick::create(new $driver());
    }

    /**
     * @param string $content
     * @param string $model
     * @param int $maxCharLength
     * @return string
     * @throws \Exception
     */
    public function summarize (
        string $content,
        int $maxCharLength = 500) : string
    {
        $systemPrompt = "Your task is to take the given text provided by the user and summarize it in under $maxCharLength characters.";

        $response = $this->sidekick->complete()->sendMessage(
            model: $this->sidekick->defaultCompleteModel,
            systemPrompt: $systemPrompt,
            message: $content);

        return $this->sidekick->getResponse($response) ?? throw new \Exception("Something went wrong, please try again later.");
    }

    /**
     * @param string $text
     * @param string $model
     * @return array
     * @throws \Exception
     */
    public function extractKeywords(
        string $text,
    ): string {

        $response = $this->sidekick->complete()->sendMessage(
            model: $this->sidekick->defaultCompleteModel,
            systemPrompt: "Extract important keywords from the following text and separate them by commas:",
            message: $text
        );

        return $this->sidekick->getResponse($response)  ?? throw new \Exception("Something went wrong, please try again later.");
    }

    /**
     * @param string $text
     * @param string $targetLanguage
     * @return string
     * @throws \Exception
     */
    public function translateText(
        string $text,
        string $targetLanguage,
    ): string {
        $systemPrompt = "Translate the following text to {$targetLanguage}:";

        $response = $this->sidekick->complete()->sendMessage(
            model: $this->sidekick->defaultCompleteModel,
            systemPrompt: $systemPrompt,
            message: $text
        );

        return $this->sidekick->getResponse($response) ?? throw new \Exception("Something went wrong, please try again later.");
    }

    /**
     * @param string $prompt
     * @param int $maxTokens
     * @return string
     * @throws \Exception
     */
    public function generateContent(
        string $prompt,
        int $maxTokens = 1024
    ): string {

        $response = $this->sidekick->complete()->sendMessage(
            model: $this->sidekick->defaultCompleteModel,
            systemPrompt: "Generate content based on the following prompt:",
            message: $prompt,
            maxTokens: $maxTokens
        );

        return $this->sidekick->getResponse($response)  ?? throw new \Exception("Something went wrong, please try again later.");
    }
}
