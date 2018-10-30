<?php

namespace App\Service;

use App\Entity\League;
use App\Entity\Team;
use App\Exception\BadRequestHttpException;
use App\Repository\TeamRepository;
use App\Transformer\ViolationDataTransformer;
use App\Validator\TeamRequestParametersValidator;
use Doctrine\ORM\EntityManagerInterface;

class TeamManager extends BaseManager
{
    const PARAM_NAME = 'name';
    const PARAM_STRIP = 'strip';
    const PARAM_LEAGUE_ID = 'league_id';
    
    /** @var TeamRequestParametersValidator $parametersValidator */

    /** @var TeamRepository $teamRepository */
    private $teamRepository;

    /** @var LeagueManager $leagueManager */
    private $leagueManager;

    /**
     * TeamManager constructor.
     *
     * @param EntityManagerInterface         $entityManager
     * @param TeamRequestParametersValidator $parametersValidator
     * @param ViolationDataTransformer       $violationDataTransformer
     * @param TeamRepository                 $teamRepository
     * @param LeagueManager                  $leagueManager
     */
    public function __construct(
        EntityManagerInterface $entityManager, 
        TeamRequestParametersValidator $parametersValidator,
        ViolationDataTransformer $violationDataTransformer,
        TeamRepository $teamRepository,
        LeagueManager $leagueManager
    ) {
        
        $this->teamRepository = $teamRepository;
        $this->leagueManager = $leagueManager;
        
        parent::__construct($entityManager, $parametersValidator, $violationDataTransformer);
    }
    
    /**
     * @param int $id
     *
     * @return Team|null
     */
    public function get(int $id): ?Team
    {
        return $this->teamRepository->getOne($id);
    }

    /**
     * @param array $parameters
     *
     * @return Team|null
     */
    public function create(array $parameters): ?Team
    {
        $violations = $this->getValidator()->validate($parameters);
        
        if (!count($violations)) {
            
            $league = $this->leagueManager->get($parameters[self::PARAM_LEAGUE_ID]);
            
            if (isset($league) && $league) {

                $team = Team::create(
                    $parameters[self::PARAM_NAME], 
                    $parameters[self::PARAM_STRIP], 
                    $league
                );
                $this->entityManager->persist($team);
                $this->entityManager->flush($team);

                return $team;

            }
        } else {
            throw new BadRequestHttpException($this->violationDataTransformer->transform($violations));
        }

        return null;
    }

    /**
     * @param Team  $team
     * @param array $parameters
     *
     * @return Team|null
     */
    public function update(Team $team, array $parameters): ?Team
    {
        $violations = $this->getValidator()->validate($parameters);
        
        if (!count($violations)) {
            
            $league = $this->leagueManager->get($parameters[self::PARAM_LEAGUE_ID]);
            
            if (isset($league) && $league) {

                $team
                    ->setName($parameters[self::PARAM_NAME])
                    ->setStrip($parameters[self::PARAM_STRIP])
                    ->setLeague($league);

                $this->entityManager->flush($team);

                return $team;
            }
            
        } else {
            throw new BadRequestHttpException($this->violationDataTransformer->transform($violations));
        }

        return null;
    }

    /**
     * @param League $league
     *
     * @return Team[]|null
     */
    public function getByLeague(League $league): array
    {
        return $this->teamRepository->findByLeague($league);
    }
}