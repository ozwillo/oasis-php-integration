<?php
/**
 * oasis-php-integration - PHP library for accessing the OASIS service.
 * Copyright (C) 2014  Aurélien Ponçon, Thomas Broyer, Xavier Calland
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

use Pekkis\Clock\ClockProvider;
use PoleNumerique\Oasis\Authz\StateSerializer;
use PoleNumerique\Oasis\Exception\AuthorizationResponseException;
use PoleNumerique\Oasis\Exception\HttpException;
use PoleNumerique\Oasis\Exception\OasisException;
use PoleNumerique\Oasis\Exception\TokenResponseException;
use PoleNumerique\Oasis\Tools\HttpClient;

class ExchangeCodeForTokenRequestBuilder
{
    private $httpClient;
    private $tokenValidator;
    private $stateSerializer;

    private $tokenEndpoint;
    private $expectedIssuer;
    private $clientId;
    private $clientPassword;

    private $authorizationCode;
    private $redirectUri;
    private $actualState;
    private $expectedState;
    private $expectedNonce;
    private $timeout;

    public function __construct(HttpClient $httpClient, StateSerializer $stateSerializer, TokenValidator $tokenValidator,
                                $clientId, $clientPassword, $tokenEndpoint, $expectedIssuer)
    {
        $this->httpClient = $httpClient;
        $this->tokenValidator = $tokenValidator;
        $this->stateSerializer = $stateSerializer;
        $this->clientId = $clientId;
        $this->clientPassword = $clientPassword;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->expectedIssuer = $expectedIssuer;
    }

    /**
     * Parse the provided uri and get the related authorization code.
     *
     * @param $queryParams string|array Query parameters with query parameters used by the Authorization endpoint for redirect the end user to your application
     * @throws \PoleNumerique\Oasis\Exception\AuthorizationResponseException
     */
    public function setAuthorizationResponse($queryParams)
    {
        $unserializedQueryParams = $queryParams;
        if (is_string($queryParams)) {
            parse_str($queryParams, $unserializedQueryParams);
        }
        if (isset($unserializedQueryParams['error'])) {
            throw new AuthorizationResponseException($unserializedQueryParams['error'],
                isset($unserializedQueryParams['error_description']) ? $unserializedQueryParams['error_description'] : null);
        }

        $this->authorizationCode = $unserializedQueryParams['code'];
        $this->actualState = $unserializedQueryParams['state'];
        return $this;
    }

    /**
     * Same URI that the one provided into Authorization request
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function setExpectedState($expectedState)
    {
        $this->expectedState = $expectedState;
        return $this;
    }

    public function setExpectedNonce($expectedNonce)
    {
        $this->expectedNonce = $expectedNonce;
        return $this;
    }

    /**
     * Indicates the timeout of the request
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return array array(accessToken, refreshToken, idToken, state)
     * @throws \PoleNumerique\Oasis\Exception\TokenResponseException
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    public function execute()
    {
        if (!$this->redirectUri) {
            throw new OasisException('Missing redirect_uri parameter');
        }
        if (!$this->authorizationCode) {
            throw new OasisException('Missing code parameter');
        }
        if ($this->actualState !== $this->expectedState) {
            throw new OasisException('Expected and received states are different.');
        }
        try {
            $response = $this->httpClient->post($this->tokenEndpoint, array(
                'params' => array(
                    TokenRequestParameter::CODE => $this->authorizationCode,
                    TokenRequestParameter::REDIRECT_URI => $this->redirectUri,
                    TokenRequestParameter::GRANT_TYPE => 'authorization_code'
                ),
                'auth' => array(
                    'username' => $this->clientId,
                    'password' => $this->clientPassword
                ),
                'timeout' => $this->timeout
            ));
        } catch (HttpException $e) {
            throw new OasisException('Token endpoint unreachable', $e);
        }
        $responseData = $response->toJson();

        $unserializedState = $this->stateSerializer->unserialize($this->actualState);
        // 400 is the usual status code when the token endpoint returns an error
        // see http://openid.net/specs/openid-connect-core-1_0.html#TokenErrorResponse
        if ($response->getStatusCode() === 400) {
            throw new TokenResponseException(
                isset($responseData['error']) ? $responseData['error'] : null,
                isset($responseData['error_description']) ? $responseData['error_description'] : null,
                $response->getStatusCode(),
                $unserializedState
            );
        } else if ($response->getStatusCode() !== 200) {
            throw new OasisException('Error while trying to get an access token from an authorization code [Status=' . $response->getStatusCode() . ']');
        }

        $idToken = $this->tokenValidator->validateAndGetIdToken($responseData['id_token'], $this->clientId,
            $this->expectedIssuer, $this->expectedNonce);

        // XXX: Even if the OpenID specification specify that the scope might not be included in the Token response,
        // there isn't any verification on $responseData['scope'] presence because it's always sent by the Oasis server
        $authorizedScope = explode(' ', $responseData['scope']);
        $now = ClockProvider::getClock()->getTime();
        return array(
            new AccessToken($responseData['access_token'], $authorizedScope, $responseData['expires_in'], $now),
            isset($responseData['refresh_token']) ? new Token($responseData['refresh_token'], $authorizedScope) : null,
            $idToken,
            $unserializedState
        );
    }
}