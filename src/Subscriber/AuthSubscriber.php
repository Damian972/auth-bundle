<?php

namespace Damian972\AuthBundle\Subscriber;

use Damian972\AuthBundle\Event\BadCredentialsEvent;
use Damian972\AuthBundle\Service\LoginAttempt;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoginAttempt
     */
    private $loginAttempt;

    public function __construct(LoginAttempt $loginAttempt)
    {
        $this->loginAttempt = $loginAttempt;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // RegisteredUserEvent::class => 'onNewUser',
            // AccountConfirmedEvent::class => 'onAccountConfirmed',
            // PasswordResetRequestEvent::class => 'onPasswordResetRequest',
            // PasswordRecoveredEvent::class => 'onPasswordRecovered',
            BadCredentialsEvent::class => 'onBadCredentials',
        ];
    }

    public function onBadCredentials(BadCredentialsEvent $event): void
    {
        $this->loginAttempt->addOne($event->getUser());
    }
}
