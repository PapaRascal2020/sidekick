<?php

namespace PapaRascalDev\Sidekick\Providers\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use PapaRascalDev\Sidekick\Exceptions\ProviderException;

trait HandlesHttpRequests
{
    protected function http(): PendingRequest
    {
        $request = Http::baseUrl($this->config['base_url'])
            ->timeout(config('sidekick.http.timeout', 30))
            ->connectTimeout(config('sidekick.http.connect_timeout', 10));

        $retryTimes = config('sidekick.http.retry.times', 0);
        if ($retryTimes > 0) {
            $request->retry($retryTimes, config('sidekick.http.retry.sleep', 100));
        }

        return $this->applyAuth($request);
    }

    abstract protected function applyAuth(PendingRequest $request): PendingRequest;

    protected function post(string $url, array $data): array
    {
        $response = $this->http()->post($url, $data);

        $this->throwIfFailed($response);

        return $response->json();
    }

    protected function postRaw(string $url, array $data): string
    {
        $response = $this->http()->post($url, $data);

        $this->throwIfFailed($response);

        return $response->body();
    }

    protected function postMultipart(string $url, array $multipart): array
    {
        $request = $this->http()->asMultipart();

        foreach ($multipart as $field) {
            $request->attach($field['name'], $field['contents'], $field['filename'] ?? null);
        }

        $response = $request->post($url);

        $this->throwIfFailed($response);

        return $response->json();
    }

    protected function throwIfFailed(Response $response): void
    {
        if ($response->failed()) {
            throw ProviderException::fromResponse($response, $this->name());
        }
    }
}
