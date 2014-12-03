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

use PoleNumerique\Oasis\Tools\HttpResponse;

class RevocationRequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    const CLIENT_ID = 'Abraham Simpson';
    const CLIENT_SECRET = 'Abe';
    const REVOCATION_ENDPOINT = 'http://oasis.example.com/a/revoke';
    const SERIALIZED_ACCESS_TOKEN = 'QmFjayBpbiBteSBkYXkgd2UgY2FsbGVkIHNhbmR3aWNoZXMgImZsYXQgYnJlYWR5Ii4gSXQgY29zdCBmb3VyIHBsYXlpbmcgY2FyZHMgYSBiaXRlLg==';

    private $accessToken;

    function __construct()
    {
        // Only The first parameter is useful in the following tests
        $this->accessToken = new AccessToken(self::SERIALIZED_ACCESS_TOKEN, array(), time(), time());
    }

    public function testValidRevocationRequest()
    {
        $revocationRequestBuilder = new RevocationRequestBuilder(self::CLIENT_ID, self::CLIENT_SECRET,
            $this->mockHttpClient(1), self::REVOCATION_ENDPOINT);
        $revocationRequestBuilder
            ->setToken($this->accessToken)
            ->execute();
        // If it goes here, it means the test is successful
    }

    public function testRevocationRequestWithSerializedToken()
    {
        $revocationRequestBuilder = new RevocationRequestBuilder(self::CLIENT_ID, self::CLIENT_SECRET,
            $this->mockHttpClient(1), self::REVOCATION_ENDPOINT);
        $revocationRequestBuilder
            ->setToken(self::SERIALIZED_ACCESS_TOKEN)
            ->execute();
        // If it goes here, it means the test is successful
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testRevocationRequestWithoutToken()
    {
        $revocationRequestBuilder = new RevocationRequestBuilder(self::CLIENT_ID, self::CLIENT_SECRET,
            $this->mockHttpClient(0), self::REVOCATION_ENDPOINT);
        $revocationRequestBuilder
            ->execute();
    }

    private function mockHttpClient($nTimes)
    {
        $httpClientObserver = $this->getMock('\PoleNumerique\Oasis\Tools\HttpClient', array('post'));
        if ($nTimes) {
            $expectedHttpResponse = HttpResponse::fromCurlResponse('', array(), array(
                'http_code' => 200
            ));

            $httpClientObserver->expects($this->exactly($nTimes))
                ->method('post')
                ->with(self::REVOCATION_ENDPOINT, $this->callback(function ($options) {
                    return strtolower($options['auth']['method']) === 'basic' &&
                        $options['auth']['username'] === self::CLIENT_ID &&
                        $options['auth']['password'] === self::CLIENT_SECRET &&
                        $options['params']['token'] === self::SERIALIZED_ACCESS_TOKEN;
                }))
                ->willReturn($expectedHttpResponse);
        } else {
            $httpClientObserver->expects($this->never())
                ->method('post')
                ->with(self::REVOCATION_ENDPOINT, $this->anything());
        }
        return $httpClientObserver;
    }
} 