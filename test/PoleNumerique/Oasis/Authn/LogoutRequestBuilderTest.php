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

namespace PoleNumerique\Oasis\Authn;

class LogoutRequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    const END_SESSION_ENDPOINT = 'http://oasis.example.com/a/logout';
    const POST_LOGOUT_REDIRECT_URI = 'http://app.example.com/post-logout';
    const ID_TOKEN_HINT = 'TGUgY29sb25lbCBtb3V0YXJkZSBhIHR16SBsZSBwcm9mZXNzZXVyIGRhbnMgbGEgY3Vpc2luZSBhdmVjIHVuIGNoYW5kZWxpZXIu';
    const STATE = 'cluedo';

    public function testValidLogoutRequestCreation()
    {
        $logoutRequestBuilder = new LogoutRequestBuilder(self::END_SESSION_ENDPOINT);
        $logoutUri = $logoutRequestBuilder
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setPostLogoutRedirectUri(self::POST_LOGOUT_REDIRECT_URI)
            ->setState(self::STATE)
            ->buildUri();
        $this->assertLogoutUriValid($logoutUri, self::POST_LOGOUT_REDIRECT_URI, self::STATE);
    }

    public function testLogoutRequestCreationWithDefaultPostLogoutUri()
    {
        $logoutRequestBuilder = new LogoutRequestBuilder(self::END_SESSION_ENDPOINT, self::POST_LOGOUT_REDIRECT_URI);
        $logoutUri = $logoutRequestBuilder
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setState(self::STATE)
            ->buildUri();
        $this->assertLogoutUriValid($logoutUri, self::POST_LOGOUT_REDIRECT_URI, self::STATE);
    }


    public function testLogoutRequestCreationWithoutPostLogoutUri()
    {
        $logoutRequestBuilder = new LogoutRequestBuilder(self::END_SESSION_ENDPOINT);
        $logoutUri = $logoutRequestBuilder
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->buildUri();
        // It's not necessary to test state because it may be forget by the API as there is not any post_logout_redirect_uri
        $this->assertLogoutUriValid($logoutUri);
    }


    public function testLogoutRequestCreationWithoutState()
    {
        $logoutRequestBuilder = new LogoutRequestBuilder(self::END_SESSION_ENDPOINT);
        $logoutUri = $logoutRequestBuilder
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setPostLogoutRedirectUri(self::POST_LOGOUT_REDIRECT_URI)
            ->buildUri();
        $this->assertLogoutUriValid($logoutUri, self::POST_LOGOUT_REDIRECT_URI);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testLogoutRequestCreationWithoutIdTokenHint()
    {
        $logoutRequestBuilder = new LogoutRequestBuilder(self::END_SESSION_ENDPOINT);
        $logoutRequestBuilder
            ->setPostLogoutRedirectUri(self::POST_LOGOUT_REDIRECT_URI)
            ->setState(self::STATE)
            ->buildUri();
    }

    private function assertLogoutUriValid($logoutUri, $postLogoutUri = null, $state = null)
    {
        $parsedUri = parse_url($logoutUri);

        // Check the base uri
        $this->assertEquals(self::END_SESSION_ENDPOINT, $parsedUri['scheme'] . '://' . $parsedUri['host'] . $parsedUri['path']);

        $queryParams = array();
        parse_str($parsedUri['query'], $queryParams);
        $this->assertEquals(self::ID_TOKEN_HINT, $queryParams[LogoutRequestParameter::ID_TOKEN_HINT]);
        if ($postLogoutUri) {
            $this->assertEquals($postLogoutUri, $queryParams[LogoutRequestParameter::POST_LOGOUT_REDIRECT_URI]);
        }
        if ($state) {
            $this->assertEquals($state, $queryParams[LogoutRequestParameter::STATE]);
        }
    }
}