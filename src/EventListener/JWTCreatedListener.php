<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        /** @var User $user */
        $user    = $event->getUser();
        $payload = $event->getData();

        $payload['profile']['first_name']   = $user->getProfile()->getFirstName();
        $payload['profile']['last_name']    = $user->getProfile()->getLastName();

        $event->setData($payload);
    }
}
