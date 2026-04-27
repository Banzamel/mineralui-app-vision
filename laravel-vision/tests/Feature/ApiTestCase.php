<?php

namespace Tests\Feature;

use Administration\Models\Company;
use Auth\Models\User;
use Database\Seeders\RoleAndPermissionsSeeder;
use Database\Seeders\UsersCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionsSeeder::class);
        $this->seed(UsersCompanySeeder::class);

        $this->company = Company::where('slug', 'vision-demo')->first();
        DB::table('sec_companies')
            ->where('id', $this->company->id)
            ->update(['expired_at' => now()->addYear()]);
        $this->company->refresh();

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->operator = User::where('email', 'user@example.com')->first();
    }

    protected function actingAsAdmin(): static
    {
        Passport::actingAs($this->admin, ['api']);
        setPermissionsTeamId($this->admin->company_id);
        return $this;
    }

    protected function actingAsOperator(): static
    {
        Passport::actingAs($this->operator, ['api']);
        setPermissionsTeamId($this->operator->company_id);
        return $this;
    }

    protected function actingAsUser(User $user): static
    {
        Passport::actingAs($user, ['api']);
        setPermissionsTeamId($user->company_id);
        return $this;
    }
}
