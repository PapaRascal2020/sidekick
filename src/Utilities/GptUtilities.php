<?php

namespace PapaRascalDev\Sidekick\Utilities;

use Illuminate\Http\Client\ConnectionException;
use PapaRascalDev\Sidekick\Drivers\OpenAi;
use PapaRascalDev\Sidekick\Sidekick;

/****************************************************************
 *                                                              *
 *  OpenAi Helper                                               *
 *                                                              *
 *  This is a set of helper functions for people using          *
 *  OpenAi models                                               *
 *                                                              *
 ****************************************************************/

class GptUtilities extends Utilities
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
        $response = $this->sidekick->moderate()->text(model: $model, content: $content);

        if(!isset($response['results']['categories'])) return false;

        foreach($response['results']['categories'] as $category => $bool) {
            if (!in_array($category, $exclusions) && $bool) return true;
        }

        return false;
    }

    /**
     * @param string $data
     * @param string|null $mimeType
     * @return string
     * @throws \Exception
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
                throw new \Exception("Cannot create file. Invalid data passed.");
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
        return $filePath;
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
     * @throws \Exception
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

        return $mimeTypes[$mimeType] ?? throw new \Exception("Mime-type $mimeType is not supported");
    }
}
