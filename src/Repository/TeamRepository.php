<?php

namespace App\Repository;

use App\Entity\League;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @param int $id
     *
     * @return Team|null
     */
    public function getOne(int $id): ?Team
    {
        return $this->find($id);
    }

    /**
     * @param string $name
     *
     * @return Team|null
     */
    public function findByName(string $name): ?Team
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @param League $league
     *
     * @return Team[]|null
     */
    public function findByLeague(League $league): ?array
    {
        return $this->findBy(['league' => $league]);
    }
}
