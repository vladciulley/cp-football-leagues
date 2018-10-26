<?php

namespace App\Service;

use App\Entity\League;
use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;

class TeamManager extends BaseManager
{

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @var TeamRepository $teamRepository
     */
    private $teamRepository;

    /**
     * @var LeagueManager $leagueManager
     */
    private $leagueManager;

    /**
     * TeamManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TeamRepository         $teamRepository
     * @param LeagueManager          $leagueManager
     */
    public function __construct(EntityManagerInterface $entityManager, TeamRepository $teamRepository, LeagueManager $leagueManager)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
        $this->leagueManager = $leagueManager;
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
     * @param string $name
     * @param string $strip
     * @param int    $leagueId
     *
     * @return Team|null
     */
    public function create(string $name, string $strip, int $leagueId): ?Team
    {
        $league = $this->leagueManager->get($leagueId);

        if ($league) {

            $team = Team::create($name, $strip, $league);
            $this->entityManager->persist($team);
            $this->entityManager->flush($team);

            return $team;

        }

        return null;
    }

    /**
     * @param Team   $team
     * @param string $name
     * @param string $strip
     * @param int    $leagueId
     *
     * @return Team|null
     */
    public function update(Team $team, string $name, string $strip, int $leagueId): ?Team
    {
        $league = $this->leagueManager->get($leagueId);

        if ($league) {

            $team
                ->setName($name)
                ->setStrip($strip)
                ->setLeague($league);

            $this->entityManager->flush($team);

            return $team;

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
        return $this->teamRepository->findBy(['league' => $league]);
    }
}