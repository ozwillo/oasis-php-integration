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
use PoleNumerique\Oasis\Tools\HttpClient;

class OasisBuilder
{
    private $clientId;
    private $clientPassword;
    private $defaultRedirectUri;
    private $providerConfiguration;

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

    public function build()
    {
        if (!$this->clientId || !$this->clientPassword) {
            throw new OasisException('Missing credentials configuration.');
        }
        if (!$this->providerConfiguration) {
            throw new OasisException('Missing provider configuration.');
        }
        return new Oasis($this->clientId, $this->clientPassword, $this->defaultRedirectUri, $this->providerConfiguration,
            new HttpClient(), new StateSerializer());
    }
}