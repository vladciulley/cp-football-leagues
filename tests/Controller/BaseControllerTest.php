<?php

namespace App\Tests\Controller;

use App\Entity\League;
use App\Entity\Team;
use App\Entity\User;
use App\Service\LeagueManager;
use App\Service\TeamManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class BaseControllerTest extends WebTestCase
{
    
    const TEAMS_FIXTURES_KEY = 'teams';
    const LEAGUES_FIXTURES_KEY = 'leagues';
    const USERS_FIXTURES_KEY = 'users';
    
    const TEST_USER_EMAIL = 'test.user@localhost';
    const TEST_USER_PASSWORD = 'some-secret-password';
    
    /** @var array $fixtures */
    private $fixtures = [];
    
    /** @var Client $client */
    private $client;
    
    public function setUp(): void
    {
        $this->initClient();
        static::loadTestFixtures();
    }

    public function tearDown(): void
    {
        $this->client = null;
        static::deleteTestFixtures();
        
        parent::tearDown();
    }

    /**
     * Initialize the test client
     */
    private function initClient(): void
    {
        $this->client = static::createClient();
        $this->client->catchExceptions(true);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param null   $token
     * @param array  $params
     *
     * @return Response
     */
    protected function request($method, $uri, $token = null, $params = []): Response
    {
        $content = null;
        $server = ['CONTENT_TYPE' => 'application/json'];
        
        if ($token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }
        
        if (!empty($params)) {
            $content = json_encode($params);
        }
        
        
        $this->client->request(
            $method,
            $uri,
            $params,
            [],
            $server,
            $content
        );
        
        $response = $this->client->getResponse();
        
        $this->assertThat(
            $response->headers->get('Content-Type'),
            $this->logicalOr(
                $this->equalTo('application/json'),
                $this->isNull()
            )
        );

        return $response;
    }

    /**
     * Retrieves the JWT authentication token
     *
     * @return string
     */
    protected function getJwtToken(): string
    {
        $response = $this->request('POST', '/login', null, [
            'username' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
        ]);
        
        $responseData = $this->getResponseData($response);
        
        if (is_array($responseData) && array_key_exists('token', $responseData)) {
            return $responseData['token'];
        }
        
        return '';
    }

    /**
     * Generates database data for tests
     */
    protected function loadTestFixtures(): void
    {
        // Users
        $user = User::create(self::TEST_USER_EMAIL);
        $encodedPassword = $this->getService('security.user_password_encoder.generic')->encodePassword($user, self::TEST_USER_PASSWORD);
        $user->setPassword($encodedPassword);
        
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        
        $this->setFixtures(self::USERS_FIXTURES_KEY, $this->extractFixturesEmails([$user]));
        
        // Leagues
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
        
        // Teams
        $teams = [
            Team::create('Test Team 1', 'color/white', $nonEmptyLeague),
            Team::create('Test Team 2', 'color/black', $nonEmptyLeague),
            Team::create('Test Team 3', 'color/green', $nonEmptyLeague),
        ];
        
        foreach ($teams as $team) {
            $this->getEntityManager()->persist($team);
        }
        
        $this->getEntityManager()->flush();
        
        $this->setFixtures(self::LEAGUES_FIXTURES_KEY, $this->extractFixturesIds($leagues));
        $this->setFixtures(self::TEAMS_FIXTURES_KEY, $this->extractFixturesIds($teams));
        
        $this->getEntityManager()->clear();
    }

    /**
     * Database test data cleanup
     */
    protected function deleteTestFixtures(): void
    {
        // Users
        /** @var UserProviderInterface $userProvider */
        $userProvider = $this->getService('security.user.provider.concrete.app_user_provider');
        $userEmail = $this->getOneFixture(self::USERS_FIXTURES_KEY);
        $user = $userProvider->loadUserByUsername($userEmail);
        
        if ($user){
            $this->getEntityManager()->remove($user);
            $this->getEntityManager()->flush($user);
        }
        
        // Teams
        /** @var TeamManager $teamManager */
        $teamManager = $this->getService(TeamManager::class);
        
        foreach ($this->getFixtures(self::TEAMS_FIXTURES_KEY) as $id) {
            
            $team = $teamManager->get($id);
            
            if ($team) {
                $this->getEntityManager()->remove($team);
            }
        }
        
        // Leagues
        /** @var LeagueManager $leagueManager */
        $leagueManager = $this->getService(LeagueManager::class);

        foreach ($this->getFixtures(self::LEAGUES_FIXTURES_KEY) as $id) {
            
            $league = $leagueManager->get($id);
            
            if ($league) {
                $this->getEntityManager()->remove($league);
            }
        }
        
        $this->getEntityManager()->flush();
    }

    /**
     * @param Response $response
     *
     * @return array|null
     */
    public function getResponseData(Response $response): ?array 
    {
        return json_decode($response->getContent(), true);
    }

    /**
     * @param $id
     *
     * @return object
     */
    protected function getService($id): object
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    /**
     * @return ManagerRegistry|object
     */
    protected function getDoctrine(): ManagerRegistry
    {
        return $this->getService('doctrine');
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @param string $key
     * @param array  $fixturesIds
     */
    protected function setFixtures(string $key, array $fixturesIds): void
    {
        $this->fixtures[$key] = $fixturesIds;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    protected function getFixtures(string $key): array
    {
        return array_key_exists($key, $this->fixtures) ? $this->fixtures[$key] : [];
    }

    /**
     * @param string $key
     *
     * @return int|string
     */
    protected function getOneFixture(string $key)
    {
        $ids = $this->getFixtures($key);
        
        if (!empty($ids)) {
            return $ids[0];
        }
        
        return null;
    }
    
    /**
     * @param array $entities
     *
     * @return array
     */
    protected function extractFixturesIds($entities): array 
    {
        $ids = [];
        
        foreach ($entities as $entity) {
            $ids[] = $entity->getId();
        }
        
        return $ids;
    }
    
    /**
     * @param array $entities
     *
     * @return array
     */
    protected function extractFixturesEmails($entities): array 
    {
        $emails = [];
        
        foreach ($entities as $entity) {
            $emails[] = method_exists($entity, 'getEmail') ? $entity->getEmail() : '';
        }
        
        return $emails;
    }

}
