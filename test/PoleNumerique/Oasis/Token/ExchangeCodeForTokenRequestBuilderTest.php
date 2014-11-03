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

use Pekkis\Clock\ClockProvider;
use Pekkis\Clock\FixedClock;
use PoleNumerique\Oasis\Tools\HttpResponse;

class ExchangeCodeForTokenRequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    const TOKEN_ENDPOINT = 'https://my.oasis.eu/token';
    const CLIENT_ID = 'Marge';
    const CLIENT_PASSWORD = 'Bouvier';
    const EXPIRES_IN = 3600;
    const ACCESS_TOKEN = 'Vulyb25pcXVlIEF1Z2VyZWF1';
    const ID_TOKEN = 'I confirm this token is valid';
    const SERIALIZED_STATE = 'SSBhbSBzZXJpYWxpemVk';
    const UNKNOWN_SERIALIZED_STATE = 'Tm9ib2R5IGtub3dzIG1lIDooIA==';
    const STATE = 'some state';
    const NONCE = 'something extremely random like qwertyuiop';
    const SCOPE = 'openid datacore';
    const AUTHORIZATION_CODE = 'RWlmZmVsIDY1';
    const REDIRECT_URI = 'some redirect uri';

    public function testValidAccessTokenRequest()
    {
        ClockProvider::setClock(new FixedClock(time()));

        $expectedTokenResponseData = array(
            'access_token' => self::ACCESS_TOKEN,
            'token_type' => 'Bearer',
            'expires_in' => self::EXPIRES_IN,
            'id_token' => self::ID_TOKEN,
            'scope' => self::SCOPE
        );

        $expectedHttpResponse = HttpResponse::fromCurlResponse(json_encode($expectedTokenResponseData), array(
            'http_code' => 200
        ));

        $httpClientObserver = $this->mockHttpClient($expectedHttpResponse);
        $stateSerializerObserver = $this->mockStateSerializer(self::SERIALIZED_STATE);

        $exchangeCodeForTokenRequestBuilder = new ExchangeCodeForTokenRequestBuilder($httpClientObserver, $stateSerializerObserver,
            self::CLIENT_ID, self::CLIENT_PASSWORD, self::TOKEN_ENDPOINT);
        list($accessToken, , $idToken, $state) = $exchangeCodeForTokenRequestBuilder
            ->setAuthorizationResponse(array(
                'code' => self::AUTHORIZATION_CODE,
                'state' => self::SERIALIZED_STATE
            ))
            ->setRedirectUri(self::REDIRECT_URI)
            ->setExpectedState(self::SERIALIZED_STATE)
            ->setExpectedNonce(self::NONCE)
            ->execute();
        $this->assertAccessTokenValid($expectedTokenResponseData, $accessToken, $idToken, $state);
    }

    public function testAccessTokenRequestWithSerializedAuthorizationRequest()
    {
        ClockProvider::setClock(new FixedClock(time()));

        $expectedTokenResponseData = array(
            'access_token' => self::ACCESS_TOKEN,
            'token_type' => 'Bearer',
            'expires_in' => self::EXPIRES_IN,
            'id_token' => self::ID_TOKEN,
            'scope' => self::SCOPE
        );

        $expectedHttpResponse = HttpResponse::fromCurlResponse(json_encode($expectedTokenResponseData), array(
            'http_code' => 200
        ));

        $httpClientObserver = $this->mockHttpClient($expectedHttpResponse);
        $stateSerializerObserver = $this->mockStateSerializer(self::SERIALIZED_STATE);

        $exchangeCodeForTokenRequestBuilder = new ExchangeCodeForTokenRequestBuilder($httpClientObserver, $stateSerializerObserver,
            self::CLIENT_ID, self::CLIENT_PASSWORD, self::TOKEN_ENDPOINT);
        list($accessToken, , $idToken, $state) = $exchangeCodeForTokenRequestBuilder
            ->setAuthorizationResponse('code=' . self::AUTHORIZATION_CODE . '&state=' . self::SERIALIZED_STATE)
            ->setRedirectUri(self::REDIRECT_URI)
            ->setExpectedState(self::SERIALIZED_STATE)
            ->setExpectedNonce(self::NONCE)
            ->execute();
        $this->assertAccessTokenValid($expectedTokenResponseData, $accessToken, $idToken, $state);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testAccessTokenRequestWithoutRedirectUri()
    {
        $httpClientObserver = $this->mockHttpClient();
        $stateSerializerObserver = $this->mockStateSerializer(self::SERIALIZED_STATE);

        $exchangeCodeForTokenRequestBuilder = new ExchangeCodeForTokenRequestBuilder($httpClientObserver, $stateSerializerObserver,
            self::CLIENT_ID, self::CLIENT_PASSWORD, self::TOKEN_ENDPOINT);
        $exchangeCodeForTokenRequestBuilder
            ->setAuthorizationResponse(array(
                'code' => self::AUTHORIZATION_CODE,
                'state' => self::SERIALIZED_STATE
            ))
            ->setExpectedState(self::SERIALIZED_STATE)
            ->setExpectedNonce(self::NONCE)
            ->execute();
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testAccessTokenRequestWithoutAuthorizationResponse()
    {
        $httpClientObserver = $this->mockHttpClient();
        $stateSerializerObserver = $this->mockStateSerializer(self::SERIALIZED_STATE);

        $exchangeCodeForTokenRequestBuilder = new ExchangeCodeForTokenRequestBuilder($httpClientObserver, $stateSerializerObserver,
            self::CLIENT_ID, self::CLIENT_PASSWORD, self::TOKEN_ENDPOINT);
        $exchangeCodeForTokenRequestBuilder
            ->setRedirectUri(self::REDIRECT_URI)
            ->setExpectedState(self::SERIALIZED_STATE)
            ->setExpectedNonce(self::NONCE)
            ->execute();
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\AuthorizationResponseException
     */
    public function testAccessTokenRequestWithWrongAuthorizationResponse()
    {
        $httpClientObserver = $this->mockHttpClient();
        $stateSerializerObserver = $this->mockStateSerializer(self::SERIALIZED_STATE);

        $exchangeCodeForTokenRequestBuilder = new ExchangeCodeForTokenRequestBuilder($httpClientObserver, $stateSerializerObserver,
            self::CLIENT_ID, self::CLIENT_PASSWORD, self::TOKEN_ENDPOINT);
        $exchangeCodeForTokenRequestBuilder->setAuthorizationResponse(array('error' => 'login_required'));
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\TokenResponseException
     */
    public function testAccessTokenRequestWithWrongTokenResponse()
    {
        $expectedTokenResponseData = array(
            'error' => 'invalid_request'
        );
        $expectedHttpResponse = HttpResponse::fromCurlResponse(json_encode($expectedTokenResponseData), array(
            'http_code' => 400
        ));

        $httpClientObserver = $this->mockHttpClient($expectedHttpResponse);
        $stateSerializerObserver = $this->mockStateSerializer(self::SERIALIZED_STATE);

        $exchangeCodeForTokenRequestBuilder = new ExchangeCodeForTokenRequestBuilder($httpClientObserver, $stateSerializerObserver,
            self::CLIENT_ID, self::CLIENT_PASSWORD, self::TOKEN_ENDPOINT);
        $exchangeCodeForTokenRequestBuilder
            ->setAuthorizationResponse(array(
                'code' => self::AUTHORIZATION_CODE,
                'state' => self::SERIALIZED_STATE
            ))
            ->setRedirectUri(self::REDIRECT_URI)
            ->setExpectedState(self::SERIALIZED_STATE)
            ->setExpectedNonce(self::NONCE)
            ->execute();
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testAccessTokenRequestWithWrongState()
    {
        $httpClientObserver = $this->mockHttpClient();
        $stateSerializerObserver = $this->mockStateSerializer(self::UNKNOWN_SERIALIZED_STATE);

        $exchangeCodeForTokenRequestBuilder = new ExchangeCodeForTokenRequestBuilder($httpClientObserver, $stateSerializerObserver,
            self::CLIENT_ID, self::CLIENT_PASSWORD, self::TOKEN_ENDPOINT);
        $exchangeCodeForTokenRequestBuilder
            ->setAuthorizationResponse(array(
                'code' => self::AUTHORIZATION_CODE,
                'state' => self::UNKNOWN_SERIALIZED_STATE
            ))
            ->setRedirectUri(self::REDIRECT_URI)
            ->setExpectedState(self::SERIALIZED_STATE)
            ->setExpectedNonce(self::NONCE)
            ->execute();
    }

    public function assertAccessTokenValid($expectedTokenResponseData, $accessToken, $idToken, $state)
    {
        $this->assertEquals($expectedTokenResponseData['access_token'], $accessToken->getCode());
        $this->assertEquals(explode(' ', $expectedTokenResponseData['scope']), $accessToken->getScope());
        $this->assertEquals($expectedTokenResponseData['expires_in'] + ClockProvider::getClock()->getTime(), $accessToken->getExpiresAt());
        $this->assertEquals($expectedTokenResponseData['id_token'], $idToken);
        $this->assertEquals(self::STATE, $state);
    }

    private function mockHttpClient(HttpResponse $expectedHttpResponse = null)
    {
        $httpClientObserver = $this->getMock('\PoleNumerique\Oasis\Tools\HttpClient', array('post'));
        if ($expectedHttpResponse) {
            $httpClientObserver->expects($this->once())
                ->method('post')
                ->with(self::TOKEN_ENDPOINT, $this->callback(function($options) {
                    return $options['auth']['username'] === self::CLIENT_ID &&
                        $options['auth']['password'] === self::CLIENT_PASSWORD &&
                        $options['params']['code'] === self::AUTHORIZATION_CODE &&
                        $options['params']['grant_type'] === 'authorization_code' &&
                        $options['params']['redirect_uri'] === self::REDIRECT_URI;
                }))
                ->willReturn($expectedHttpResponse);
        } else {
            $httpClientObserver->expects($this->never())
                ->method('post')
                ->with(self::TOKEN_ENDPOINT, $this->anything());
        }
        return $httpClientObserver;
    }

    private function mockStateSerializer($serializedState)
    {
        $stateSerializerObserver = $this->getMock('\PoleNumerique\Oasis\Authz\StateSerializer', array('unserialize'));
        if ($serializedState === self::SERIALIZED_STATE) {
            $stateSerializerObserver->expects($this->any())
                ->method('unserialize')
                ->with(self::SERIALIZED_STATE)
                ->willReturn(self::STATE);
        } else {
            $stateSerializerObserver->expects($this->any())
                ->method('unserialize')
                ->with($serializedState)
                ->willReturn('unknown state');
        }

        return $stateSerializerObserver;
    }
}