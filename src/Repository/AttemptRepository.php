<?php

namespace Damian972\AuthBundle\Repository;

use Damian972\AuthBundle\Contracts\UserInterface;
use Damian972\AuthBundle\Entity\Attempt;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attempt::class);
    }

    public function countRecentFor(UserInterface $user, int $minutes): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id) as count')
            ->where('l.user = :user')
            ->andWhere('l.createdAt > :date')
            ->setParameter('date', new DateTimeImmutable("-{$minutes} minutes"))
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
