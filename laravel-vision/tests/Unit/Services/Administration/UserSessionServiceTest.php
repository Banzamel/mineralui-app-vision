<?php

namespace Tests\Unit\Services\Administration;

use Administration\Dtos\CompanySessionsQueryDto;
use Administration\Dtos\RevokeSessionDto;
use Administration\Repositories\Interfaces\UserRepositoryInterface;
use Administration\Services\UserSessionService;
use Auth\Repositories\Interfaces\AuthLogRepositoryInterface;
use Auth\Repositories\Interfaces\TokenRepositoryInterface;
use Mockery;
use ReflectionClass;
use Shared\Exceptions\ApiJsonException;
use Tests\TestCase;

class UserSessionServiceTest extends TestCase
{
    private UserRepositoryInterface $users;
    private TokenRepositoryInterface $tokens;
    private AuthLogRepositoryInterface $authLogs;
    private UserSessionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = Mockery::mock(UserRepositoryInterface::class);
        $this->tokens = Mockery::mock(TokenRepositoryInterface::class);
        $this->authLogs = Mockery::mock(AuthLogRepositoryInterface::class);
        $this->service = new UserSessionService($this->users, $this->tokens, $this->authLogs);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_returns_empty_when_company_has_no_users(): void
    {
        $this->users->shouldReceive('idsByCompany')->once()->with(50)->andReturn([]);

        $dto = new CompanySessionsQueryDto(companyId: 50, currentTokenId: null);
        $this->assertSame([], $this->service->listCompanySessions($dto));
    }

    public function test_revoke_session_throws_when_user_not_in_company(): void
    {
        $this->users->shouldReceive('findInCompany')->once()->with(7, 100)->andReturn(null);

        $this->expectException(ApiJsonException::class);
        $this->service->revokeSession(new RevokeSessionDto(userId: 7, companyId: 100, sessionId: 'abc'));
    }

    public function test_revoke_session_throws_when_token_not_found(): void
    {
        $owner = new \Administration\Models\User();
        $this->users->shouldReceive('findInCompany')->once()->andReturn($owner);
        $this->tokens->shouldReceive('findForUser')->once()->with('abc', 7)->andReturn(null);

        $this->expectException(ApiJsonException::class);
        $this->service->revokeSession(new RevokeSessionDto(userId: 7, companyId: 100, sessionId: 'abc'));
    }

    public function test_detect_device_classifies_common_user_agents(): void
    {
        $this->assertSame('iOS', $this->callDetect('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0)'));
        $this->assertSame('iOS', $this->callDetect('iPad'));
        $this->assertSame('Android', $this->callDetect('Mozilla/5.0 (Linux; Android 14)'));
        $this->assertSame('Windows', $this->callDetect('Mozilla/5.0 (Windows NT 10.0)'));
        $this->assertSame('macOS', $this->callDetect('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15)'));
        $this->assertSame('Linux', $this->callDetect('Mozilla/5.0 (X11; Linux x86_64)'));
        $this->assertSame('Nieznane', $this->callDetect(null));
        $this->assertSame('Nieznane', $this->callDetect('curl/8.1'));
    }

    private function callDetect(?string $ua): string
    {
        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('detectDevice');
        $m->setAccessible(true);
        return $m->invoke($this->service, $ua);
    }
}
