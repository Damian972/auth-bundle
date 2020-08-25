<?php

namespace Damian972\AuthBundle\Exception;

use Damian972\AuthBundle\Contracts\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooManyBadCrendentialsException extends AuthenticationException
{
    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
        parent::__construct('Too many attemps, please try later', 0);
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
