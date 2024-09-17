<?php

namespace PapaRascalDev\Sidekick\Features;

use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class StreamedCompletion
{
    /**
     * @param string $url
     * @param array $headers
     * @param $requestRules
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
     * @return array|mixed
     * @throws ConnectionException
     */
    public function sendMessage(
        string          $model,
        string          $systemPrompt,
        string          $message,
        array|object    $allMessages = [],
        int             $maxTokens = 1024,
    ): Generator
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

        $request['stream'] = true;

        $response = Http::withHeaders($this->headers)
            ->withOptions(['stream' => true])
            ->post($this->url, $request);

        if (!($response instanceof \Illuminate\Http\Client\Response)) {
            throw new \Exception('Unexpected response type');
        }

        $body = $response->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);

            // Get rid of data: (if present)
            if (str_starts_with($line, 'data:')) {
                $line = trim(substr($line, strlen('data:')));
            }

            // Format to array
            $response = json_decode($line, true) ?? [];

            // Yield for processing
            yield $response;
        }
    }

    private function readLine($stream): string
    {
        $buffer = '';

        while (!$stream->eof()) {
            $byte = $stream->read(1);
            if ($byte === false) {
                return $buffer;
            }
            $buffer .= $byte;
            if ($byte === "\n") {
                break;
            }
        }

        return $buffer;
    }
}
