<?php

namespace Damian972\AuthBundle\Service;

use Damian972\AuthBundle\Contracts\UserInterface;
use Damian972\AuthBundle\Entity\PasswordResetRequest;
use Damian972\AuthBundle\Entity\ResetToken;
use Damian972\AuthBundle\Event\PasswordRecoveredEvent;
use Damian972\AuthBundle\Event\PasswordResetRequestEvent;
use Damian972\AuthBundle\Exception\OngoingPasswordResetException;
use Damian972\AuthBundle\Exception\UserNotFoundException;
use Damian972\AuthBundle\Helpers\TokenGenerator;
use Damian972\AuthBundle\Repository\TokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordReset
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var int
     */
    private $tokenExpireAfter;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenRepository $tokenRepository,
        UserPasswordEncoderInterface $encoder,
        EventDispatcherInterface $eventDispatcher,
        int $tokenExpireAfter
    ) {
        $this->entityManager = $entityManager;
        $this->tokenRepository = $tokenRepository;
        $this->encoder = $encoder;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenExpireAfter = $tokenExpireAfter;
        $this->userRepository = $this->entityManager->getRepository(UserInterface::class);
    }

    public function resetPassword(PasswordResetRequest $data): void
    {
        $user = $this->userRepository->findOneBy(['email' => $data->getEmail()]);
        if (!$user instanceof UserInterface) {
            throw new UserNotFoundException();
        }
        $token = $this->tokenRepository->findOneBy(['user' => $user]);
        if (null !== $token && !$this->isExpired($token)) {
            throw new OngoingPasswordResetException();
        }
        if (null === $token) {
            $token = new ResetToken();
        }
        $token
            ->setUser($user)
            ->setToken(TokenGenerator::generate(TokenGenerator::RESET_PASSWORD_TYPE))
        ;
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PasswordResetRequestEvent($token));
    }

    public function isExpired(ResetToken $token): bool
    {
        $expirationDate = new DateTimeImmutable('-'.$this->tokenExpireAfter.' minutes');

        return $expirationDate > $token->getCreatedAt();
    }

    public function updatePassword(string $password, ResetToken $token): void
    {
        /**
         * @var UserInterface
         */
        $user = $token->getUser();
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $this->entityManager->remove($token);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PasswordRecoveredEvent($user));
    }
}
