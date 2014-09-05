<?php

namespace Caxy\Bundle\RecurlyBundle;

class ClientFactory
{
    public static function create($subdomain, $apiKey)
    {
        \Recurly_Client::$subdomain = $subdomain;
        \Recurly_Client::$apiKey = $apiKey;

        return new \Recurly_Client();
    }
}
