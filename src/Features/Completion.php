<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Support\Facades\Http;

class Completion
{
    /**
     * @param string $url
     * @param array $headers
     * @param $requestRules
     * @param $responseFormat
     */
    function __construct(
        protected string $url,
        protected array  $headers,
        protected        $requestRules,
    )
    {
    }


    /**
     * @param string $model
     * @param string $systemPrompt
     * @param string $message
     * @param array|object $allMessages
     * @param int $maxTokens
     * @param bool $stream
     * @throws ConnectionException
     */
    public function sendMessage(
        string          $model,
        string          $systemPrompt,
        string          $message,
        array|object    $allMessages = [],
        int             $maxTokens = 1024,
        bool            $stream = false
    )
    {
        // Takes request and maps it to the $this->requestRules
        $request = [];
        foreach ($this->requestRules as $key => $value) {
            if (is_array($value)) {
                $arrayMap = [];
                foreach ($value as $k => $val) {
                    if ($eval = eval("return $val;")) {
                        if(isset($eval['role'])) {
                            $arrayMap[] = $eval;
                        } else {
                            $arrayMap = [
                                ...$arrayMap,
                                ...$eval
                            ];
                        }
                    } else {
                        unset($value[$k]);
                    }
                }
                $request[$key] = [...$arrayMap];
            } else {
                $request[$key] = eval("return $value;");
            }
        }


        return Http::withHeaders($this->headers)
            ->post($this->url, $request)->json();
    }
}
