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

namespace PoleNumerique\Oasis\Authz;

use PoleNumerique\Oasis\Exception\OasisException;

class AuthorizationRequestBuilder
{
    const RANDOM_BYTES_SIZE = 32;

    private $stateSerializer;

    private $authorizationEndpoint;
    private $clientId;
    private $redirectUri;
    private $scope = array('openid');
    private $state;
    private $prompt = array();
    private $maxAge;
    private $idTokenHint;
    private $uiLocales = array();

    function __construct(StateSerializer $stateSerializer, $authorizationEndpoint, $clientId)
    {
        $this->stateSerializer = $stateSerializer;

        $this->authorizationEndpoint = $authorizationEndpoint;
        $this->clientId = $clientId;
    }

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function setScope(array $scope)
    {
        $this->scope = $scope;
        return $this;
    }

    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    public function setPrompt(array $prompt)
    {
        $this->prompt = $prompt;
        if (in_array(PromptValue::NONE, $this->prompt) && count($this->prompt) > 1) {
            throw new OasisException('Invalid parameter value: prompt "none" cannot be associated with another prompt value');
        }
        return $this;
    }

    public function setMaxAge($maxAge)
    {
        $this->maxAge = $maxAge;
        return $this;
    }

    public function setIdTokenHint($idTokenHint)
    {
        $this->idTokenHint = $idTokenHint;
        return $this;
    }

    public function setUiLocales(array $uiLocales) {
        $this->uiLocales = $uiLocales;
        return $this;
    }

    // TODO: Add other parameters like nonce and state

    public function buildUri()
    {
        if (!$this->redirectUri) {
            throw new OasisException('Missing redirect_uri parameter');
        }

        $serializedState = $this->stateSerializer->serialize($this->state, $this->generateRandom());
        $nonce = $this->generateRandom();
        $uri = http_build_url($this->authorizationEndpoint, array(
            'query' => http_build_query(array(
                AuthorizationRequestParameter::SCOPE => implode(' ', $this->scope),
                AuthorizationRequestParameter::RESPONSE_TYPE => 'code',
                AuthorizationRequestParameter::CLIENT_ID => $this->clientId,
                AuthorizationRequestParameter::REDIRECT_URI => $this->redirectUri,
                AuthorizationRequestParameter::STATE => $serializedState,
                AuthorizationRequestParameter::NONCE => $nonce,
                AuthorizationRequestParameter::PROMPT => implode(' ', $this->prompt),
                AuthorizationRequestParameter::MAX_AGE => $this->maxAge,
                AuthorizationRequestParameter::ID_TOKEN_HINT => $this->idTokenHint,
                AuthorizationRequestParameter::UI_LOCALES => implode(' ', $this->uiLocales)
            ))
        ));
        return array($uri, $serializedState, $nonce);
    }

    private function generateRandom()
    {
        return base64_encode(openssl_random_pseudo_bytes(self::RANDOM_BYTES_SIZE));
    }
}