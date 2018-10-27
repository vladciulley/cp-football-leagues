<?php

namespace App\Tests\Controller;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BaseControllerTest extends WebTestCase
{
    
    const TEAMS_FIXTURES_KEY = 'teams';
    const LEAGUES_FIXTURES_KEY = 'leagues';
    
    /** @var array $fixturesIds */
    private $fixturesIds = [];
    
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

    abstract protected function loadTestFixtures(): void;

    abstract protected function deleteTestFixtures(): void;
    
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
        
        try {
            $this->client->request(
                $method,
                $uri,
                $params,
                [],
                $server,
                $content
            );
        } catch (NotFoundHttpException $e) {
            return new Response(json_encode(['message' => $e->getMessage()]), $e->getStatusCode());
        }

        return $this->client->getResponse();
    }
    
    /**
     * Retrieves the JWT authentication token
     *
     * @return string
     */
    protected function getJwtToken(): string
    {
        $response = $this->request('POST', '/login', null, [
            'username' => 'user1@localdev',
            'password' => 'pass1',
        ]);
        
        $responseData = $this->getResponseData($response);
        
        if (is_array($responseData) && array_key_exists('token', $responseData)) {
            return $responseData['token'];
        }
        
        return '';
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
     * @param $class
     *
     * @return ServiceEntityRepository
     */
    protected function getRepository($class): ServiceEntityRepository
    {
        return $this->getDoctrine()->getRepository($class);
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

}
