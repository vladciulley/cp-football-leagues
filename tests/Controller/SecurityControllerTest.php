<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityControllerTest extends BaseControllerTest
{
    public function testMethodNotAllowed(): void
    {
        $tests = [
            [
                'method' => 'GET',
                'uri' => '/login',
            ],
        ];
        
        foreach ($tests as $test) {
            $response = $this->request($test['method'], $test['uri'], null, [
                'username' => self::TEST_USER_EMAIL,
                'password' => self::TEST_USER_PASSWORD,
            ]);
            $this->assertEquals(JsonResponse::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        }
    }
    
    public function testLogin(): void
    {
        $response = $this->request('POST', '/login', null, [
            'username' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
        ]);
        
        $responseData = $this->getResponseData($response);
        
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertTrue(is_array($responseData));
        $this->assertEquals(2, count($responseData));
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertNotEmpty($responseData['user']);
        $this->assertNotEmpty($responseData['token']);
    }
    
    public function testBadCredentialsLogin(): void
    {
        $response = $this->request('POST', '/login', null, [
            'username' => self::TEST_USER_EMAIL,
            'password' => 'bad-password',
        ]);
        
        $responseData = $this->getResponseData($response);
        
        $this->assertEquals(JsonResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertTrue(is_array($responseData));
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid credentials.', $responseData['error']);
        
        
        $response = $this->request('POST', '/login', null, [
            'username' => 'bad-username',
            'password' => self::TEST_USER_PASSWORD,
        ]);
        
        $responseData = $this->getResponseData($response);
        
        $this->assertEquals(JsonResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
        
        $this->assertTrue(is_array($responseData));
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid credentials.', $responseData['error']);
        
    }
    
    public function testUnauthorizedAccess(): void
    {
        $leagueId = $this->getOneFixture(self::LEAGUES_FIXTURES_KEY);
        
        $response = $this->request('GET', '/leagues/' . $leagueId);

        $this->assertEquals(JsonResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
