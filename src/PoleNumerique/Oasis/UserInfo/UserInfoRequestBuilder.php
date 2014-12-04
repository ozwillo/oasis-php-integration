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

namespace PoleNumerique\Oasis\UserInfo;

use PoleNumerique\Oasis\Exception\HttpException;
use PoleNumerique\Oasis\Exception\OasisException;
use PoleNumerique\Oasis\Exception\OAuthException;
use PoleNumerique\Oasis\Token\AccessToken;
use PoleNumerique\Oasis\Tools\HttpClient;

class UserInfoRequestBuilder
{
    private $httpClient;

    private $userInfoEndpoint;
    private $accessToken;
    private $userInfoValidator;
    private $clientId;
    private $expectedIssuer;

    function __construct(HttpClient $httpClient, UserInfoValidator $userInfoValidator, $userInfoEndpoint, $clientId, $expectedIssuer)
    {
        $this->httpClient = $httpClient;
        $this->userInfoValidator = $userInfoValidator;
        $this->userInfoEndpoint = $userInfoEndpoint;
        $this->clientId = $clientId;
        $this->expectedIssuer = $expectedIssuer;
    }

    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getUserInfo()
    {
        if (!$this->accessToken) {
            throw new OasisException('Missing access_token parameter');
        }
        try {
            $response = $this->httpClient->get($this->userInfoEndpoint, array(
                'auth' => array(
                    'method' => HttpClient::AUTH_BEARER,
                    'token' => $this->accessToken->getCode()
                ),
                'headers' => array(
                    'Accept' => 'application/jwt'
                )
            ));
        } catch (HttpException $e) {
            throw new OasisException('UserInfo endpoint unreachable', $e);
        }
        if (in_array($response->getStatusCode(), array(400, 401, 403))) {
            $headerValue = $response->getHeader('WWW-Authenticate');
            $outputArray = array();
            preg_match("/error=\"(.*)\"/U", $headerValue, $outputArray);
            $errorMessage = count($outputArray) > 1 ? $outputArray[1] : "Error while trying to get User information";
            throw new OAuthException($response->getStatusCode(), $errorMessage);
        }
        if ($response->getStatusCode() !== 200) {
            throw new OasisException('Error while trying to get user information [Status=' . $response->getStatusCode() . ']');
        }
        $userInfoRaw = $response->getRawBody();
        return $this->userInfoValidator->getValidatedUserInfo($userInfoRaw, $this->clientId, $this->expectedIssuer);
    }
}