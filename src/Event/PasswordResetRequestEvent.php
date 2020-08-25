<?php

namespace Damian972\AuthBundle\Event;

use Damian972\AuthBundle\Entity\ResetToken;

class PasswordResetRequestEvent extends AuthEvent
{
    /**
     * @var ResetToken
     */
    private $token;

    public function __construct(ResetToken $token)
    {
        $this->token = $token;
        parent::__construct($token->getUser());
    }

    public function getToken(): ResetToken
    {
        return $this->token;
    }
}
