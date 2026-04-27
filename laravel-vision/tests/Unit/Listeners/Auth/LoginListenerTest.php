<?php

namespace Tests\Unit\Listeners\Auth;

use Auth\Events\Listeners\LoginListener;
use Auth\Events\LoginEvent;
use Auth\Models\AuthLog;
use Auth\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class LoginListenerTest extends TestCase
{
    public function test_handle_writes_an_auth_log_entry_with_request_metadata(): void
    {
        // Bind a real Request into the container so `request()->ip()` / `userAgent()` resolve.
        // Mocking the Request facade trips over Laravel's setUserResolver rebind in tests.
        $request = Request::create('/login', 'POST', [], [], [], [
            'HTTP_USER_AGENT' => 'PHPUnit/1.0',
            'REMOTE_ADDR' => '10.0.0.5',
        ]);
        $this->app->instance('request', $request);

        // Intercept AuthLog::create() at the model-event layer.
        AuthLog::unguard();
        $captured = null;
        AuthLog::creating(function (AuthLog $log) use (&$captured) {
            $captured = $log->getAttributes();
            return false;
        });

        $user = new User();
        $user->id = 42;
        $user->company_id = 7;

        (new LoginListener())->handle(new LoginEvent($user));

        $this->assertSame('login', $captured['action']);
        $this->assertSame(User::class, $captured['model']);
        $this->assertSame(42, $captured['user_id']);
        $this->assertSame(7, $captured['company_id']);
        $this->assertSame('10.0.0.5', $captured['ip_address']);
        $this->assertSame('PHPUnit/1.0', $captured['user_agent']);

        AuthLog::flushEventListeners();
    }
}
