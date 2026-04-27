<?php

namespace Tests\Unit\Services\Auth;

use Auth\Services\SocialAuthService;
use ReflectionClass;
use Shared\Exceptions\ApiJsonException;
use Tests\TestCase;

class SocialAuthServiceTest extends TestCase
{
    private SocialAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SocialAuthService();
    }

    public function test_redirect_rejects_unsupported_provider(): void
    {
        $this->expectException(ApiJsonException::class);
        $this->expectExceptionMessage('Unsupported social provider: github');
        $this->service->redirect('github');
    }

    public function test_callback_rejects_unsupported_provider(): void
    {
        $this->expectException(ApiJsonException::class);
        $this->service->callback('twitter');
    }

    public function test_validate_provider_accepts_google_and_facebook(): void
    {
        // Passing through validateProvider() without throwing is the success signal — Socialite
        // call would happen next. We exercise it via reflection so we don't need a real Socialite.
        $ref = new ReflectionClass($this->service);
        $method = $ref->getMethod('validateProvider');
        $method->setAccessible(true);

        $method->invoke($this->service, 'google');
        $method->invoke($this->service, 'facebook');

        $this->assertTrue(true, 'No exception means accepted');
    }
}
