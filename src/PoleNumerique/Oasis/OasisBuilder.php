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

namespace PoleNumerique\Oasis;

use PoleNumerique\Oasis\Authz\StateSerializer;
use PoleNumerique\Oasis\Exception\OasisException;
use PoleNumerique\Oasis\Token\TokenValidator;
use PoleNumerique\Oasis\Tools\Cache;
use PoleNumerique\Oasis\Tools\HttpClient;
use PoleNumerique\Oasis\Tools\JwtVerifier;
use PoleNumerique\Oasis\Tools\KeysProvider;

class OasisBuilder
{

    private $httpClient;

    private $clientId;
    private $clientPassword;
    private $defaultRedirectUri;
    private $providerConfiguration;
    private $cache;

    function __construct($httpClient = null)
    {
        $this->httpClient = $httpClient ? $httpClient : new HttpClient();
    }

    public function setCredentials($clientId, $clientPassword)
    {
        $this->clientId = $clientId;
        $this->clientPassword = $clientPassword;
        return $this;
    }

    public function setDefaultRedirectUri($redirectUri)
    {
        $this->defaultRedirectUri = $redirectUri;
        return $this;
    }

    public function setProviderConfig(array $providerConfiguration)
    {
        $this->providerConfiguration = $providerConfiguration;
        return $this;
    }

    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    public function build()
    {
        if (!$this->clientId || !$this->clientPassword) {
            throw new OasisException('Missing credentials configuration.');
        }
        if (!$this->providerConfiguration) {
            throw new OasisException('Missing provider configuration.');
        }
        if (!$this->cache) {
            throw new OasisException('Missing cache.');
        }
        $keysProvider = new KeysProvider($this->cache, $this->httpClient, $this->clientId, $this->clientPassword, $this->providerConfiguration['jwks_uri']);
        return new Oasis($this->clientId, $this->clientPassword, $this->defaultRedirectUri, $this->providerConfiguration,
            new TokenValidator(new JwtVerifier($keysProvider)), $this->httpClient, new StateSerializer());
    }
}