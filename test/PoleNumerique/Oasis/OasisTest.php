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

use PoleNumerique\Oasis\Authz\AuthorizationRequestParameter;

class OasisTest extends \PHPUnit_Framework_TestCase
{
    const CLIENT_ID = 'Barney Gumble';
    const REDIRECT_URI = 'https://app.example.com';
    const ANOTHER_REDIRECT_URI = 'https://another-app.example.com';

    private static $providerConfig = array('authorization_endpoint' => 'https://oasis.example.org/authz');

    public function testAuthorizationRequestInitialization()
    {
        $oasis = Oasis::builder()
            ->setProviderConfig(self::$providerConfig)
            ->setCredentials(self::CLIENT_ID, 'beer')
            ->build();
        list($uri) = $oasis->initAuthorizationRequest()
            ->setRedirectUri(self::REDIRECT_URI)
            ->buildUri();
        $queryParams = array();
        parse_str(parse_url($uri, PHP_URL_QUERY), $queryParams);
        $this->assertEquals(self::CLIENT_ID, $queryParams[AuthorizationRequestParameter::CLIENT_ID]);
    }

    public function testAuthorizationRequestInitializationWithDefaultRedirectUri()
    {
        $oasis = Oasis::builder()
            ->setProviderConfig(self::$providerConfig)
            ->setCredentials(self::CLIENT_ID, 'beer')
            ->setDefaultRedirectUri(self::REDIRECT_URI)
            ->build();
        list($uri) = $oasis->initAuthorizationRequest()
            ->buildUri();
        $queryParams = array();
        parse_str(parse_url($uri, PHP_URL_QUERY), $queryParams);
        $this->assertEquals(self::REDIRECT_URI, $queryParams[AuthorizationRequestParameter::REDIRECT_URI]);
    }

    public function testAuthorizationRequestInitializationWithDefaultRedirectUriOverridden()
    {
        $oasis = Oasis::builder()
            ->setProviderConfig(self::$providerConfig)
            ->setCredentials(self::CLIENT_ID, 'beer')
            ->setDefaultRedirectUri(self::REDIRECT_URI)
            ->build();
        list($uri) = $oasis->initAuthorizationRequest()
            ->setRedirectUri(self::ANOTHER_REDIRECT_URI)
            ->buildUri();
        $queryParams = array();
        parse_str(parse_url($uri, PHP_URL_QUERY), $queryParams);
        $this->assertEquals(self::ANOTHER_REDIRECT_URI, $queryParams[AuthorizationRequestParameter::REDIRECT_URI]);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testInitializationWithoutProviderConfig()
    {
        Oasis::builder()
            ->setCredentials(self::CLIENT_ID, 'beer')
            ->build();
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testInitializationWithoutCredentials()
    {
        Oasis::builder()
            ->setProviderConfig(self::$providerConfig)
            ->build();
    }
}