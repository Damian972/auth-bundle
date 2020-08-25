<?php

namespace Damian972\AuthBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OngoingPasswordResetException extends AuthenticationException
{
    /**
     * @var string
     */
    protected $message = 'An reset action is already going on, please try later';
}
