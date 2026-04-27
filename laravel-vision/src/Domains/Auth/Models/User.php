<?php

namespace Auth\Models;

use Laravel\Passport\HasApiTokens;
use Administration\Models\User as BaseUser;

/**
 * User model in the authorization context, extended with Passport API tokens.
 */
class User extends BaseUser
{
    use HasApiTokens;
}
