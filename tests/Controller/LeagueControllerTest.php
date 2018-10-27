<?php

namespace App\Tests\Controller;

use App\Entity\League;
use App\Entity\Team;
use App\Service\LeagueManager;
use App\Service\TeamManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class LeagueControllerTest extends BaseControllerTest
{
    protected function loadTestFixtures(): void
    {
        $leagues = [
            League::create('Test League One (with teams)'),
            League::create('Test League Two (empty)'),
        ];
        
        foreach ($leagues as $league) {
            $this->getEntityManager()->persist($league);
        }
        
        $this->getEntityManager()->flush();
        
        /** @var LeagueManager $leagueManager */
        $leagueManager = $this->getService(LeagueManager::class);
        $nonEmptyLeague = $leagueManager->getByName('Test League One (with teams)');
        
        $teams = [
            Team::create('Test Team 1', 'color/white', $nonEmptyLeague),
            Team::create('Test Team 2', 'color/black', $nonEmptyLeague),
            Team::create('Test Team 3', 'color/green', $nonEmptyLeague),
        ];
        
        foreach ($teams as $team) {
            $this->getEntityManager()->persist($team);
        }
        
        $this->getEntityManager()->flush();
        
        $this->setFixturesIds(self::LEAGUES_FIXTURES_KEY, $this->extractTestFixturesIds($leagues));
        $this->setFixturesIds(self::TEAMS_FIXTURES_KEY, $this->extractTestFixturesIds($teams));
        
        $this->getEntityManager()->clear();
    }

    protected function deleteTestFixtures(): void
    {
        /** @var TeamManager $teamManager */
        $teamManager = $this->getService(TeamManager::class);
        
        foreach ($this->getFixturesIds(self::TEAMS_FIXTURES_KEY) as $id) {
            
            $team = $teamManager->get($id);
            
            if ($team) {
                $this->getEntityManager()->remove($team);
            }
        }
        
        /** @var LeagueManager $leagueManager */
        $leagueManager = $this->getService(LeagueManager::class);

        foreach ($this->getFixturesIds(self::LEAGUES_FIXTURES_KEY) as $id) {
            
            $league = $leagueManager->get($id);
            
            if ($league) {
                $this->getEntityManager()->remove($league);
            }
        }
        
        $this->getEntityManager()->flush();
    }
    
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
            $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode(), $test['method'] . ' ' . $test['uri'] . ' with token ' . $token);
        }
    }
    
    public function testGetLeagues(): void
    {
        $token = $this->getJwtToken();
        $leagueId = $this->getOneFixtureId(self::LEAGUES_FIXTURES_KEY);
        
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
        $leagueId = $this->getOneFixtureId(self::LEAGUES_FIXTURES_KEY);
        
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
        $leagueId = $this->getOneFixtureId(self::LEAGUES_FIXTURES_KEY);

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
