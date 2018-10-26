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
        
        list($code, $body) = $this->request('GET', '/teams/0', $token);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $code);
        
        list($code, $body) = $this->request('PUT', '/teams/0', $token);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $code);
        
    }
    
    public function testGetTeams(): void
    {
        $token = $this->getJwtToken();
        $teamId = $this->getOneFixtureId(self::TEAMS_FIXTURES_KEY);
        
        /** @var TeamManager $teamManager */
        $teamManager = $this->getService(TeamManager::class);
        $team = $teamManager->get($teamId);

        list($code, $body) = $this->request('GET', '/teams/' . $teamId, $token);

        $this->assertEquals(JsonResponse::HTTP_OK, $code);
        $this->assertTrue(is_array($body));
        $this->assertEquals(3, count($body));
        $this->assertArrayHasKey('name', $body);
        $this->assertArrayHasKey('strip', $body);
        $this->assertArrayHasKey('league', $body);
        $this->assertTrue(is_string($body['name']));
        $this->assertStringStartsWith('Test Team', $body['name']);
        $this->assertEquals($body['name'], $team->getName());
        $this->assertEquals($body['strip'], $team->getStrip());
        $this->assertEquals($body['league'], $team->getLeague()->getName());
        
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
        
        list($code, $body) = $this->request('POST', '/teams', $token, $badParams);
        
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $code);
        
        
        $goodParams = [
            'name' => 'New Team Name',
            'strip' => 'red/blue',
            'league_id' => $leagueId
        ];
        
        list($code, $body) = $this->request('POST', '/teams', $token, $goodParams);
        
        $this->assertEquals(JsonResponse::HTTP_CREATED, $code);
        $this->assertEmpty($body);
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
        
        list($code, $body) = $this->request('PUT', '/teams/' . $teamId, $token, $badParams);
        
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $code);
        
        
        $goodParams = [
            'name' => 'Updated Team Name',
            'strip' => 'gray/orange',
            'league_id' => $leagueId
        ];
        
        list($code, $body) = $this->request('PUT', '/teams/' . $teamId, $token, $goodParams);
        
        $this->assertEquals(JsonResponse::HTTP_NO_CONTENT, $code);
        $this->assertEmpty($body);
        
        
        $this->getEntityManager()->clear();
        
        /** @var TeamManager $teamManager */
        $teamManager = $this->getService(TeamManager::class);
        $team = $teamManager->get($teamId);
        
        $this->assertEquals($team->getName(), $goodParams['name']);
        $this->assertEquals($team->getStrip(), $goodParams['strip']);
        $this->assertEquals($team->getLeague()->getId(), $goodParams['league_id']);
    }
}
