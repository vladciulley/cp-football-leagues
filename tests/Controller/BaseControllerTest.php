<?php

namespace App\Tests\Controller;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseControllerTest extends WebTestCase
{
    
    const TEAMS_FIXTURES_KEY = 'teams';
    const LEAGUES_FIXTURES_KEY = 'leagues';
    
    /** @var array $fixturesIds */
    private $fixturesIds = [];
    
    /** @var Client $httpClient */
    private $httpClient;
    
    public function setUp(): void
    {
        self::bootKernel();
        $this->initHttpClient();
        static::loadTestFixtures();
    }

    public function tearDown(): void
    {
        $this->httpClient = null;
        static::deleteTestFixtures();
        
        parent::tearDown();
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
    protected function getContainer()
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
     * @param $class
     *
     * @return ServiceEntityRepository
     */
    protected function getRepository($class): ServiceEntityRepository
    {
        return $this->getDoctrine()->getRepository($class);
    }

    /**
     * Initialize the http client
     */
    private function initHttpClient(): void
    {
        $this->httpClient = new Client([
            'base_uri' => 'http://localhost:8000',
        ]);
    }

    /**
     * @param string $key
     * @param array  $ids
     */
    protected function setFixturesIds(string $key, array $ids): void
    {
        $this->fixturesIds[$key] = $ids;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    protected function getFixturesIds(string $key): array
    {
        return array_key_exists($key, $this->fixturesIds) ? $this->fixturesIds[$key] : [];
    }

    /**
     * @param string $key
     *
     * @return int|null
     */
    protected function getOneFixtureId(string $key): ?int
    {
        $ids = $this->getFixturesIds($key);
        
        if (!empty($ids)) {
            return $ids[0];
        }
        
        return null;
    }

    /**
     * Retrieves the JWT authentication token
     *
     * @return string
     */
    protected function getJwtToken(): string
    {
        list($code, $body) = $this->request('POST', '/login', null, [
            'username' => 'user1@localdev',
            'password' => 'pass1',
        ]);

        return $body['token'];
    }


    abstract protected function loadTestFixtures(): void;

    abstract protected function deleteTestFixtures(): void;
    
    /**
     * @param array $entities
     *
     * @return array
     */
    protected function extractTestFixturesIds($entities): array 
    {
        $ids = [];
        
        foreach ($entities as $entity) {
            $ids[] = $entity->getId();
        }
        
        return $ids;
    }

    /**
     * @param string $method
     * @param string $path
     * @param null   $token
     * @param array  $params
     *
     * @return array [code, [body]]
     */
    protected function request($method, $path, $token = null, $params = []): array
    {
        $options = [];

        if (!empty($params)) {
            $options['json'] = $params;
        }

        if ($token) {
            $options['headers'] = [
                'Authorization' => 'Bearer ' . $token,
            ];
        }

        try {
            $response = $this->httpClient->request($method, $path, $options);
        } catch (GuzzleException $e) {

            return [
                $e->getCode(),
                ['message' => $e->getMessage()],
            ];
        }

        return [
            $response->getStatusCode(),
            json_decode($response->getBody()->getContents(), true),
        ];
    }

}
