<?php

declare(strict_types=1);

namespace Tests;

use Hypervel\Foundation\Testing\Concerns\RunTestsInCoroutine;
use Hypervel\Foundation\Testing\Http\TestResponse;
use Hypervel\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RunTestsInCoroutine;

    /**
     * Send a PATCH request to the application as JSON.
     */
    protected function patchJson($uri, array $data = [], array $headers = []): TestResponse
    {
        $cookies = $this->withCredentials ? $this->defaultCookies : [];

        $response = $this->createTestResponse(
            $this->getTestingClient()->json(
                'PATCH',
                $this->prepareUrlForRequest($uri),
                $data,
                array_merge($this->defaultHeaders, $headers),
                $cookies
            )
        );

        if ($this->followRedirects) {
            $response = $this->followRedirects($response);
        }

        $this->flushRequestStates();

        return $response;
    }
}
