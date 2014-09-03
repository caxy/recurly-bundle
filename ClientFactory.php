<?php

namespace Caxy\Bundle\RecurlyBundle;

class ClientFactory
{
    static function create($subdomain, $apiKey)
    {
        \Recurly_Client::$subdomain = $subdomain;
        return new \Recurly_Client($apiKey);
    }
}
