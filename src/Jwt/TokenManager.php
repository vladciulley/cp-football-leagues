<?php

namespace App\Jwt;

use ReallySimpleJWT\Exception\TokenValidatorException;
use ReallySimpleJWT\Token;

class TokenManager
{
    const TOKEN_TTL = 60; // minutes
    
    /** @var string $secret */
    private $secret;

    /** @var string $issuer */
    private $issuer;

    /** @var int $tokenTTL */
    private $tokenTTL;

    /**
     * TokenManager constructor.
     *
     * @param string $secret
     * @param string $issuer
     * @param int    $tokenTTL
     */
    public function __construct(string $secret, string $issuer, int $tokenTTL)
    {
        $this->secret = $secret;
        $this->issuer = $issuer;
        $this->tokenTTL = $tokenTTL;
    }

    /**
     * @param string $userId
     *
     * @return string
     * @throws \Exception
     */
    public function generateToken(string $userId): string
    {
        $expiration = new \DateTime();
        $expiration->add(new \DateInterval('PT' . $this->tokenTTL . 'M'));

        $token = Token::getToken(
            $userId,
            $this->secret,
            $expiration->getTimestamp(),
            $this->issuer
        );

        return $token;
    }

    /**
     * @param string $token
     *
     * @return bool
     * @throws TokenValidatorException
     */
    public function validateToken(string $token): bool
    {
        return Token::validate($token, $this->secret);
    }

    /**
     * @param string $token
     *
     * @return Payload|null
     */
    public function getPayload(string $token): ?Payload
    {
        $payload = Token::getPayload($token);

        $payloadArr = json_decode($payload, true);

        if ($payloadArr) {
            return Payload::create(
                $payloadArr['user_id'],
                $payloadArr['iss'],
                $payloadArr['exp']
            );
        }

        return null;
    }
}
