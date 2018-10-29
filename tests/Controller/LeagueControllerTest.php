<?php

namespace App\Tests\Controller;

use App\Service\LeagueManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class LeagueControllerTest extends BaseControllerTest
{
    public function testNotFound(): void 
    {
        $token = $this->getJwtToken();
        
        $tests = [
            [
                'method' => 'GET',
                'uri' => '/leagues/0',
            ],
            [
                'method' => 'GET',
                'uri' => '/leagues/0/teams',
            ],
            [
                'method' => 'DELETE',
                'uri' => '/leagues/0',
            ],
        ];
        
        foreach ($tests as $test) {
            $response = $this->request($test['method'], $test['uri'], $token);
            $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
            $this->assertJsonStringEqualsJsonString(json_encode($this->notFoundJson), $response->getContent());
        }
    }
    
    public function testMethodNotAllowed()
    {
        $test = [
            'method' => 'GET',
            'uri' => '/login',
        ];
        
        $response = $this->request($test['method'], $test['uri']);
        $this->assertEquals(JsonResponse::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($this->methodNotAllowedJson), $response->getContent());
    }
    
    public function testGetLeagues(): void
    {
        $token = $this->getJwtToken();
        $leagueId = $this->getOneFixture(self::LEAGUES_FIXTURES_KEY);
        
        /** @var LeagueManager $leagueManager */
        $leagueManager = $this->getService(LeagueManager::class);
        $league = $leagueManager->get($leagueId);

        $response = $this->request('GET', '/leagues/' . $leagueId, $token);

        $responseData = $this->getResponseData($response);
        
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertTrue(is_array($responseData));
        $this->assertEquals(1, count($responseData));
        $this->assertArrayHasKey('name', $responseData);
        $this->assertEquals($league->getName(), $responseData['name']);
        
        $this->getEntityManager()->clear();
    }
    
    public function testGetLeaguesTeams(): void
    {
        $token = $this->getJwtToken();
        $leagueId = $this->getOneFixture(self::LEAGUES_FIXTURES_KEY);
        
        /** @var LeagueManager $leagueManager */
        $leagueManager = $this->getService(LeagueManager::class);
        $league = $leagueManager->get($leagueId);

        $response = $this->request('GET', '/leagues/' . $leagueId . '/teams', $token);

        $responseData = $this->getResponseData($response);

        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertTrue(is_array($responseData));
        $this->assertEquals(3, count($responseData));
        $this->assertEquals(3, count($responseData[0]));
        $this->assertArrayHasKey('name', $responseData[0]);
        $this->assertArrayHasKey('strip', $responseData[0]);
        $this->assertArrayHasKey('league', $responseData[0]);
        $this->assertTrue(is_string($responseData[0]['name']));
        $this->assertStringStartsWith('Test Team', $responseData[0]['name']);
        $this->assertEquals($league->getName(), $responseData[0]['league']);
        
        $this->getEntityManager()->clear();
    }
    
    public function testDeleteLeagues(): void
    {
        $token = $this->getJwtToken();
        $leagueId = $this->getOneFixture(self::LEAGUES_FIXTURES_KEY);

        $response = $this->request('DELETE', '/leagues/' . $leagueId, $token);
        
        $responseData = $this->getResponseData($response);

        $this->assertEquals(JsonResponse::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($responseData);
        
        /** @var LeagueManager $leagueManager */
        $leagueManager = $this->getService(LeagueManager::class);
        $league = $leagueManager->get($leagueId);
        
        $this->assertNull($league);
        
        $this->getEntityManager()->clear();
    }
}
