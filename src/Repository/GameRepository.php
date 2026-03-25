<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findWithCodeRenc(string $codeRenc): Game | null
    {
        return $this->createQueryBuilder('g')
            ->where('g.codeRenc = :code')
            ->setParameter('code', $codeRenc)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
