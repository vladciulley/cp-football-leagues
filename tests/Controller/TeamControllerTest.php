<?php

namespace App\Tests\Controller;

use App\Entity\League;
use App\Entity\Team;
use App\Service\LeagueManager;
use App\Service\TeamManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class TeamControllerTest extends BaseControllerTest
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
            Team::create('Test Team 1', 'white', $nonEmptyLeague),
            Team::create('Test Team 2', 'black', $nonEmptyLeague),
            Team::create('Test Team 3', 'green', $nonEmptyLeague),
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
                'uri' => '/teams/0',
            ],
            [
                'method' => 'PUT',
                'uri' => '/teams/0',
            ],
        ];
        
        foreach ($tests as $test) {
            $response = $this->request($test['method'], $test['uri'], $token);
            $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }
    
    
    public function testGetTeams(): void
    {
        $token = $this->getJwtToken();
        $teamId = $this->getOneFixtureId(self::TEAMS_FIXTURES_KEY);
        
        /** @var TeamManager $teamManager */
        $teamManager = $this->getService(TeamManager::class);
        $team = $teamManager->get($teamId);

        $response = $this->request('GET', '/teams/' . $teamId, $token);

        $responseData = $this->getResponseData($response);
        
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertTrue(is_array($responseData));
        $this->assertEquals(3, count($responseData));
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('strip', $responseData);
        $this->assertArrayHasKey('league', $responseData);
        $this->assertTrue(is_string($responseData['name']));
        $this->assertStringStartsWith('Test Team', $responseData['name']);
        $this->assertEquals($responseData['name'], $team->getName());
        $this->assertEquals($responseData['strip'], $team->getStrip());
        $this->assertEquals($responseData['league'], $team->getLeague()->getName());
        
        $this->getEntityManager()->clear();
    }
    
    public function testCreateTeams(): void
    {
        $token = $this->getJwtToken();
        $leagueId = $this->getOneFixtureId(self::LEAGUES_FIXTURES_KEY);
        
        
        $badParams = [
            'name' => 'New Team Name',
            'strip' => 'red/blue',
            'league_id' => '0'
        ];
        
        $response = $this->request('POST', '/teams', $token, $badParams);
        
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        
        
        $goodParams = [
            'name' => 'New Team Name',
            'strip' => 'red/blue',
            'league_id' => $leagueId
        ];
        
        $response = $this->request('POST', '/teams', $token, $goodParams);
        
        $responseData = $this->getResponseData($response);
        
        $this->assertEquals(JsonResponse::HTTP_CREATED, $response->getStatusCode());
        $this->assertEmpty($responseData);
    }
    
    public function testUpdateTeams(): void
    {
        $token = $this->getJwtToken();
        $teamId = $this->getOneFixtureId(self::TEAMS_FIXTURES_KEY);
        $leagueId = $this->getOneFixtureId(self::LEAGUES_FIXTURES_KEY);
        
        
        $badParams = [
            'name' => 'Updated Team Name',
            'strip' => 'gray/orange',
            'league_id' => '0'
        ];
        
        $response = $this->request('PUT', '/teams/' . $teamId, $token, $badParams);
        
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        
        
        $goodParams = [
            'name' => 'Updated Team Name',
            'strip' => 'gray/orange',
            'league_id' => $leagueId
        ];
        
        $response = $this->request('PUT', '/teams/' . $teamId, $token, $goodParams);
        
        $responseData = $this->getResponseData($response);
        
        $this->assertEquals(JsonResponse::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($responseData);
        
        
        $this->getEntityManager()->clear();
        
        /** @var TeamManager $teamManager */
        $teamManager = $this->getService(TeamManager::class);
        $team = $teamManager->get($teamId);
        
        $this->assertEquals($team->getName(), $goodParams['name']);
        $this->assertEquals($team->getStrip(), $goodParams['strip']);
        $this->assertEquals($team->getLeague()->getId(), $goodParams['league_id']);
    }
}
