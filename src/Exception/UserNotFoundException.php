<?php

namespace Damian972\AuthBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserNotFoundException extends AuthenticationException
{
    /**
     * @var string
     */
    protected $message = 'User not found';
}
