<?php

namespace Damian972\AuthBundle\Service;

use Damian972\AuthBundle\Contracts\UserInterface;
use Damian972\AuthBundle\Entity\Attempt;
use Damian972\AuthBundle\Repository\AttemptRepository;
use Doctrine\ORM\EntityManagerInterface;

class LoginAttempt
{
    /**
     * @var AttemptRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var int
     */
    private $maxAttempts;

    /**
     * @var int
     */
    private $expireAfter;

    public function __construct(EntityManagerInterface $entityManager, AttemptRepository $repository, int $maxAttempts = 3, int $expireAfter = 30)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->maxAttempts = $maxAttempts;
        $this->expireAfter = $expireAfter;
    }

    public function addOne(UserInterface $user): void
    {
        $attempt = (new Attempt())->setUser($user);
        $this->entityManager->persist($attempt);
        $this->entityManager->flush();
    }

    public function isLimitReached(UserInterface $user): bool
    {
        return $this->maxAttempts <= $this->repository->countRecentFor($user, $this->expireAfter);
    }
}
