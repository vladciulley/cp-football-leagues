<?php

namespace App\Tests\Controller;

use App\Entity\League;
use App\Service\LeagueManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityControllerTest extends BaseControllerTest
{
    protected function loadTestFixtures(): void
    {
        $league = League::create('Test League One');
        
        $this->getEntityManager()->persist($league);
        $this->getEntityManager()->flush();
        
        $this->setFixturesIds(self::LEAGUES_FIXTURES_KEY, $this->extractTestFixturesIds([$league]));
        
        $this->getEntityManager()->clear();
    }

    protected function deleteTestFixtures(): void
    {
        /** @var LeagueManager $leagueManager */
        $leagueManager = $this->getService(LeagueManager::class);

        $leagueId = $this->getOneFixtureId(self::LEAGUES_FIXTURES_KEY);
        $league = $leagueManager->get($leagueId);
        
        if ($league) {
            $this->getEntityManager()->remove($league);
            $this->getEntityManager()->flush($league);
        }
    }
    
    public function testUnauthorized(): void
    {
        $leagueId = $this->getOneFixtureId(self::LEAGUES_FIXTURES_KEY);
        
        list($code, $body) = $this->request('GET', '/leagues/' . $leagueId);

        $this->assertEquals(JsonResponse::HTTP_UNAUTHORIZED, $code);
    }
}
