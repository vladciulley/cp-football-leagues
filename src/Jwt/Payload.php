<?php

namespace App\Jwt;

class Payload
{
    private $issuer;

    private $userId;

    private $expire;

    /**
     * Payload constructor.
     *
     * @param $issuer
     * @param $userId
     * @param $expire
     */
    public function __construct(string $userId, string $issuer, int $expire)
    {
        $this->issuer = $issuer;
        $this->userId = $userId;
        $this->expire = $expire;
    }
    
    /**
     * @param string $userId
     * @param string $issuer
     * @param int    $expire
     *
     * @return Payload
     */
    public static function create(string $userId, string $issuer, int $expire): Payload
    {
        return new Payload($userId, $issuer, $expire);
    }

    /**
     * @return string
     */
    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    /**
     * @return string
     */
    public function getUserId(): ?string 
    {
        return $this->userId;
    }

    /**
     * @return \DateTime
     */
    public function getExpire(): \DateTime
    {
        return new \DateTime($this->expire);
    }
}
