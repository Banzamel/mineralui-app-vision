<?php

namespace Tests\Unit\Listeners\Auth;

use Auth\Events\Listeners\LogoutListener;
use Auth\Events\LogoutEvent;
use Auth\Models\AuthLog;
use Auth\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class LogoutListenerTest extends TestCase
{
    private function bindRealRequest(): void
    {
        $request = Request::create('/logout', 'POST', [], [], [], [
            'HTTP_USER_AGENT' => 'TestUA',
            'REMOTE_ADDR' => '192.168.0.1',
        ]);
        $this->app->instance('request', $request);
    }

    public function test_handle_writes_an_auth_log_entry(): void
    {
        $this->bindRealRequest();

        AuthLog::unguard();
        $captured = null;
        AuthLog::creating(function (AuthLog $log) use (&$captured) {
            $captured = $log->getAttributes();
            return false;
        });

        $user = new User();
        $user->id = 11;
        $user->company_id = 100;

        (new LogoutListener())->handle(new LogoutEvent($user));

        $this->assertSame('logout', $captured['action']);
        $this->assertSame(User::class, $captured['model']);
        $this->assertSame(11, $captured['user_id']);
        $this->assertSame(100, $captured['company_id']);

        AuthLog::flushEventListeners();
    }

    public function test_handle_falls_back_to_null_company_id_when_user_has_none(): void
    {
        $this->bindRealRequest();

        AuthLog::unguard();
        $captured = null;
        AuthLog::creating(function (AuthLog $log) use (&$captured) {
            $captured = $log->getAttributes();
            return false;
        });

        $user = new User();
        $user->id = 11;
        // company_id intentionally not set

        (new LogoutListener())->handle(new LogoutEvent($user));

        $this->assertNull($captured['company_id']);

        AuthLog::flushEventListeners();
    }
}
