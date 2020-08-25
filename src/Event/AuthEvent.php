<?php

namespace Damian972\AuthBundle\Event;

use Damian972\AuthBundle\Contracts\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AuthEvent extends Event
{
    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
