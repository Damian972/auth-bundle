<?php

namespace Damian972\AuthBundle\Contracts;

use Symfony\Component\Security\Core\User\UserInterface as SfUserInterface;

interface UserInterface extends SfUserInterface
{
    public function getEmail(): ?string;

    public function setEmail(string $email): self;

    public function getPassword(): ?string;

    public function setPassword(string $password): self;

    public function getIsActive(): bool;

    public function setIsActive(bool $isActive): self;

    public function getToken(): ?string;
}
