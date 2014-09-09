<?php

namespace Caxy\Bundle\RecurlyBundle\EventListener;

use Caxy\Bundle\RecurlyBundle\Security\RecurlyAccountInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationListener
{
    /**
     * @var \Recurly_Client
     */
    private $client;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param \Recurly_Client $client
     * @param Session         $session
     */
    public function __construct(\Recurly_Client $client, Session $session)
    {
        $this->client = $client;
        $this->session = $session;
    }

    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        $token = $event->getAuthenticationToken();

        if ($token) {
            $user = $token->getUser();

            if ($user instanceof RecurlyAccountInterface) {
                try {
                    $account = \Recurly_Account::get($user->getAccountCode());
                } catch (\Recurly_NotFoundError $e) {
                    $account = null;
                }
                $this->session->set('recurly.account', serialize($account));
            }
        }
    }
}
