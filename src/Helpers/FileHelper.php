<?php

namespace PapaRascalDev\Sidekick\Helpers;

class FileHelper
{
    public function store(string $data, string $mimeType = null)
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

    private function isBase64($data)
    {
        // Check if the data is base64 encoded
        return base64_encode(base64_decode($data, true)) === $data;
    }

    private function isBinary($data)
    {
        // Check if the data is binary
        return preg_match('~[^\x20-\x7E\t\r\n]~', $data) > 0;
    }

    private function isUrl($data)
    {
        // Check if the data is a valid URL
        return filter_var($data, FILTER_VALIDATE_URL) !== false;
    }

    private function getExtensionFromMimeType($mimeType)
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
