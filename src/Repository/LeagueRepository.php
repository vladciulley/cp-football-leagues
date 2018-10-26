<?php

namespace App\Repository;

use App\Entity\League;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method League|null find($id, $lockMode = null, $lockVersion = null)
 * @method League|null findOneBy(array $criteria, array $orderBy = null)
 * @method League[]    findAll()
 * @method League[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeagueRepository extends ServiceEntityRepository
{

    /**
     * LeagueRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, League::class);
    }

    /**
     * @param int $id
     *
     * @return League|null
     */
    public function getOne(int $id): ?League
    {
        return $this->find($id);
    }

    /**
     * @param string $name
     *
     * @return League|null
     */
    public function findByName(string $name): ?League
    {
        return $this->findOneBy(['name' => $name]);
    }

}
