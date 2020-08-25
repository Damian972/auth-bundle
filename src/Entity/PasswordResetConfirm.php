<?php

namespace Damian972\AuthBundle\Entity;

class PasswordResetConfirm
{
    /**
     * @var string
     */
    private $password = '';

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
