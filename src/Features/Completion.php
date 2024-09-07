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
        protected        $responseFormat,
    )
    {
    }


    /**
     * @param string $model
     * @param string $systemPrompt
     * @param string $message
     * @param array|object $allMessages
     * @param int $maxTokens
     * @return array
     * @throws ConnectionException
     */
    public function sendMessage(
        string $model,
        string $systemPrompt,
        string $message,
        array|object  $allMessages = [],
        int    $maxTokens = 1024
    ): array
    {
        $request = [];
        foreach ($this->requestRules as $key => $value) {
            if (is_array($value)) {
                $temp = [];
                foreach ($value as $k => $val) {
                    if ($eval = eval("return $val;")) {
                        if(isset($eval['role'])) {
                            $temp[] = $eval;
                        } else {
                            $temp = [
                                ...$temp,
                                ...$eval
                            ];
                        }
                    } else {
                        unset($value[$k]);
                    }
                }
                $request[$key] = [...$temp];
            } else {
                $request[$key] = eval("return $value;");
            }
        }

        return Http::withHeaders($this->headers)
            ->post($this->url, $request)->json();
    }
}
