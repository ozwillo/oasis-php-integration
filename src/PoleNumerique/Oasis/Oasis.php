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

use PoleNumerique\Oasis\Authz\AuthorizationRequestBuilder;
use PoleNumerique\Oasis\Authz\StateSerializer;

class Oasis
{
    private $clientId;
    private $clientPassword;
    private $defaultRedirectUri;
    private $providerConfiguration;
    private $stateSerializer;

    function __construct($clientId, $clientPassword, $defaultRedirectUri, $providerConfiguration, StateSerializer $stateSerializer = null)
    {
        $this->clientId = $clientId;
        $this->clientPassword = $clientPassword;
        $this->defaultRedirectUri = $defaultRedirectUri;
        $this->providerConfiguration = $providerConfiguration;
        $this->stateSerializer = $stateSerializer ? $stateSerializer : new StateSerializer();
    }

    public static function builder()
    {
        return new OasisBuilder();
    }

    public function initAuthorizationRequest()
    {
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($this->stateSerializer, $this->providerConfiguration['authorization_endpoint'], $this->clientId);
        if ($this->defaultRedirectUri) {
            $authorizationRequestBuilder->setRedirectUri($this->defaultRedirectUri);
        }
        return $authorizationRequestBuilder;
    }
}