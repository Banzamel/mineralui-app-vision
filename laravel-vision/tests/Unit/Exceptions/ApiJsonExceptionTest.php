<?php

namespace Tests\Unit\Exceptions;

use Shared\Exceptions\ApiJsonException;
use Tests\TestCase;

class ApiJsonExceptionTest extends TestCase
{
    public function test_default_message_and_code(): void
    {
        $exception = new ApiJsonException();

        $this->assertSame('Server error', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
    }

    public function test_custom_message_and_code(): void
    {
        $exception = new ApiJsonException('Not found', 404);

        $this->assertSame('Not found', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function test_render_returns_json_response(): void
    {
        $exception = new ApiJsonException('Validation failed', 422);

        $response = $exception->render();

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['error' => 'Validation failed'], $response->getData(true));
    }

    public function test_render_default_returns_500(): void
    {
        $exception = new ApiJsonException();

        $response = $exception->render();

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['error' => 'Server error'], $response->getData(true));
    }

    public function test_is_instance_of_exception(): void
    {
        $exception = new ApiJsonException('Test', 400);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }
}
