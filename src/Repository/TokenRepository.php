<?php

namespace Damian972\AuthBundle\Repository;

use Damian972\AuthBundle\Entity\ResetToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetToken::class);
    }
}
