<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();

        // Ajouter les permissions Mercure au payload
        $payload['mercure'] = [
            'subscribe' => ['*'],
            'publish' => ['*']
        ];

        $event->setData($payload);
    }
}
