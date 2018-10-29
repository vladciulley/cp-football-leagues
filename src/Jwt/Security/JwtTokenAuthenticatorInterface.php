<?php

namespace App\Jwt\Security;

use Symfony\Component\HttpFoundation\Request;

interface JwtTokenAuthenticatorInterface
{
    public static function extractTokenFromRequest(Request $request): string;
}