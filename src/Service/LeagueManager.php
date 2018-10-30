<?php

namespace App\Service;

use App\Entity\League;
use App\Repository\LeagueRepository;
use App\Transformer\ViolationDataTransformer;
use App\Validator\LeagueRequestParametersValidator;
use Doctrine\ORM\EntityManagerInterface;

class LeagueManager extends BaseManager
{
    /**
     * @var LeagueRepository $leagueRepository
     */
    private $leagueRepository;

    /**
     * LeagueManager constructor.
     *
     * @param EntityManagerInterface           $entityManager
     * @param LeagueRequestParametersValidator $parametersValidator
     * @param ViolationDataTransformer         $violationDataTransformer
     * @param LeagueRepository                 $leagueRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager, 
        LeagueRequestParametersValidator $parametersValidator,
        ViolationDataTransformer $violationDataTransformer,
        LeagueRepository $leagueRepository)
    {
        $this->leagueRepository = $leagueRepository;
        
        parent::__construct($entityManager, $parametersValidator, $violationDataTransformer);
    }

    /**
     * @param int $id
     *
     * @return League|null
     */
    public function get(int $id): ?League
    {
        return $this->leagueRepository->getOne($id);
    }

    /**
     * @param League $league
     */
    public function delete(League $league): void
    {
        $this->entityManager->remove($league);
        $this->entityManager->flush($league);
    }

    /**
     * @param string $name
     *
     * @return League|null
     */
    public function getByName(string $name): ?League
    {
        return $this->leagueRepository->findByName($name);
    }
}