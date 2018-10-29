<?php

namespace App\Jwt;

interface TokenManagerInterface
{
    public function generateToken(string $userId): string;

    public function validateToken(string $token): bool;

    public function getPayload(string $token): ?Payload;

}