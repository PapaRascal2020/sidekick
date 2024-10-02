<?php

namespace PapaRascalDev\Sidekick\Utilities;

use Exception;
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
    protected SidekickDriverInterface $sidekick;
    public function __construct(SidekickDriverInterface $driver) {
        $this->sidekick = Sidekick::create(new $driver());
    }

    /**
     * @param string $content
     * @param int $maxCharLength
     * @return string
     */
    public function summarize (
        string $content,
        int $maxCharLength = 500) : string
    {
        $systemPrompt = "Your task is to take the given text provided by the user and summarize it in under $maxCharLength characters.";

        return $this->sidekick->complete(
            model: $this->sidekick->defaultCompleteModel,
            systemPrompt: $systemPrompt,
            message: $content
        );

    }

    /**
     * @param string $text
     * @return string
     */
    public function extractKeywords(
        string $text,
    ): string {

        return $this->sidekick->complete(
            model: $this->sidekick->defaultCompleteModel,
            systemPrompt: "Extract important keywords from the following text and separate them by commas:",
            message: $text
        );

    }

    /**
     * @param string $text
     * @param string $targetLanguage
     * @return string
     */
    public function translateText(
        string $text,
        string $targetLanguage,
    ): string {
        $systemPrompt = "Translate the following text to {$targetLanguage}:";

        return $this->sidekick->complete(
            model: $this->sidekick->defaultCompleteModel,
            systemPrompt: $systemPrompt,
            message: $text
        );
    }

    /**
     * @param string $prompt
     * @param int $maxTokens
     * @return string
     * @throws Exception
     */
    public function generateContent(
        string $prompt,
        int $maxTokens = 1024
    ): string {

        return $this->sidekick->complete(
            model: $this->sidekick->defaultCompleteModel,
            systemPrompt: "Generate content based on the following prompt:",
            message: $prompt,
            maxTokens: $maxTokens
        );
    }

    /**
     * @param string $data
     * @param string|null $mimeType
     * @return string
     * @throws Exception
     */
    public function store(string $data, string $mimeType = null): string
    {
        // Determine the type of data
        if ($this->isBase64($data) ) {
            $data = base64_decode($data);
        } elseif ($this->isUrl($data)) {
            $data = file_get_contents($data);
        } else {
            if (!$this->isBinary($data)) {
                throw new Exception("Cannot create file. Invalid data passed.");
            }
        }

        // Determine the extension
        $ext = $this->getExtensionFromMimeType($mimeType);

        // Generate a unique filename
        $filename = uniqid() . '.' . $ext;
        $filePath = public_path('uploads/' . $filename);

        // Ensure the uploads directory exists
        if (!file_exists(public_path('uploads'))) {
            mkdir(public_path('uploads'), 0777, true);
        }

        // Store the file
        file_put_contents($filePath, $data);

        // Return the local path of the stored file
        return asset('uploads/' . $filename);
    }

    /**
     * @param $data
     * @return bool
     */
    private function isBase64($data): bool
    {
        // Check if the data is base64 encoded
        return base64_encode(base64_decode($data, true)) === $data;
    }

    /**
     * @param $data
     * @return bool
     */
    private function isBinary($data): bool
    {
        // Check if the data is binary
        return preg_match('~[^\x20-\x7E\t\r\n]~', $data) > 0;
    }

    /**
     * @param $data
     * @return bool
     */
    private function isUrl($data): bool
    {
        // Check if the data is a valid URL
        return filter_var($data, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param $mimeType
     * @return string
     * @throws Exception
     */
    private function getExtensionFromMimeType($mimeType): string
    {
        // Map MIME types to file extensions
        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'audio/mpeg' => 'mp3'
        ];

        return $mimeTypes[$mimeType] ?? throw new Exception("Mime-type $mimeType is not supported");
    }
}
