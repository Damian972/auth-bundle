<?php

namespace Damian972\AuthBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountNotActiveException extends AccountStatusException
{
    /**
     * @var string
     */
    protected $message = 'Your account is not active';
}
