<?php
/**
 * oasis-php-integration - PHP library for accessing the OASIS service.
 * Copyright (C) 2014 Atol Conseils et Développements
 *
 * This file is part of oasis-php-integration.
 *
 * oasis-php-integration is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * oasis-php-integration is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PoleNumerique\Oasis\Token;

use PoleNumerique\Oasis\Exception\HttpException;
use PoleNumerique\Oasis\Exception\OasisException;
use PoleNumerique\Oasis\Exception\TokenResponseException;
use PoleNumerique\Oasis\Tools\HttpClient;

class RevocationRequestBuilder
{
    private $clientId;
    private $clientSecret;
    private $httpClient;
    private $revocationEndpoint;

    private $token;

    function __construct($clientId, $clientSecret, HttpClient $httpClient, $revocationEndpoint)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->httpClient = $httpClient;
        $this->revocationEndpoint = $revocationEndpoint;
    }

    public function setToken($token)
    {
        $this->token = $this->serializeToken($token);
        return $this;
    }

    private function serializeToken($token)
    {
        if ($token instanceof Token) {
            return $token->getCode();
        }
        return $token;
    }

    public function execute()
    {
        if ($this->token === null) {
            throw new OasisException('Missing token parameter');
        }

        try {
            $response = $this->httpClient->post($this->revocationEndpoint, array(
                'params' => array('token' => $this->token),
                'auth' => array(
                    'method' => HttpClient::AUTH_BASIC,
                    'username' => $this->clientId,
                    'password' => $this->clientSecret
                )
            ));

            if ($response->getStatusCode() !== 200) {
                $error = $response->toJson();
                throw new TokenResponseException(
                    $error['error'],
                    isset($error['error_description']) ? $error['error_description'] : null,
                    $response->getStatusCode(),
                    null
                );
            }

        } catch (HttpException $e) {
            throw new OasisException('Revocation endpoint unreachable', $e);
        }
    }
}