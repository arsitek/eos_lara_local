<?php

use Stevenmaguire\OAuth2\Client\Provider\Keycloak;


function getSSOProvider()
{

  $provider = new Keycloak([
    'authServerUrl'         => 'https://login.usk.ac.id/auth',
    'realm'                 => 'master',
    'clientId'              => 'sirekat.usk.ac.id',
    'clientSecret'          => '57d34f26-b41a-4bc3-93c2-4607eff64070',
    'redirectUri'           => 'http://sirekat.usk.ac.id/login_sso',
  ]);

  return $provider;
}
