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
use PoleNumerique\Oasis\Authz\AuthorizationRequestParameter;
use PoleNumerique\Oasis\Authz\PromptValue;

class AuthorizationRequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    const AUTHORIZATION_ENDPOINT = 'http://oasis.example.org';
    const CLIENT_ID = 'Homer';
    const REDIRECT_URI = 'http://app.example.com';
    const STATE = 'Drinking a beer in Moe\'s tavern';
    const SERIALIZED_STATE = 'Working at the nuclear power plant';
    const DEFAULT_SERIALIZED_STATE = 'Sleeping at work';
    const ID_TOKEN_HINT = 'That\'s an ID Token';
    const MAX_AGE = 5;
    private static $validScope = array('openid', 'datacore');
    private static $validPrompt = array(PromptValue::CONSENT, PromptValue::LOGIN);
    private static $uiLocales = array('fr-CA', 'fr', 'en');

    public function testAuthorizationRequestCreationWithFullConfiguration()
    {
        $stateSerializerObserver = $this->mockStateSerializer(self::STATE);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        list($uri, $serializedState, $nonce) = $authorizationRequestBuilder
            ->setScope(self::$validScope)
            ->setRedirectUri(self::REDIRECT_URI)
            ->setState(self::STATE)
            ->setMaxAge(self::MAX_AGE)
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setPrompt(self::$validPrompt)
            ->setUiLocales(self::$uiLocales)
            ->buildUri();

        $this->assertAuthorizationRequestUriValid($uri, self::$validScope, self::SERIALIZED_STATE, $serializedState, $nonce, self::MAX_AGE,
            self::ID_TOKEN_HINT, self::$validPrompt, self::$uiLocales);
    }

    public function testAuthorizationRequestCreationWithoutScope()
    {
        $stateSerializerObserver = $this->mockStateSerializer(self::STATE);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        list($uri, $serializedState, $nonce) = $authorizationRequestBuilder
            ->setRedirectUri(self::REDIRECT_URI)
            ->setState(self::STATE)
            ->setMaxAge(self::MAX_AGE)
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setPrompt(self::$validPrompt)
            ->setUiLocales(self::$uiLocales)
            ->buildUri();

        $this->assertAuthorizationRequestUriValid($uri, array('openid'), self::SERIALIZED_STATE, $serializedState, $nonce, self::MAX_AGE,
            self::ID_TOKEN_HINT, self::$validPrompt, self::$uiLocales);
    }

    public function testAuthorizationRequestCreationWithoutState()
    {
        $stateSerializerObserver = $this->mockStateSerializer(null);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        list($uri, $serializedState, $nonce) = $authorizationRequestBuilder
            ->setScope(self::$validScope)
            ->setRedirectUri(self::REDIRECT_URI)
            ->setMaxAge(self::MAX_AGE)
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setPrompt(self::$validPrompt)
            ->setUiLocales(self::$uiLocales)
            ->buildUri();

        $this->assertAuthorizationRequestUriValid($uri, self::$validScope, self::DEFAULT_SERIALIZED_STATE, $serializedState, $nonce, self::MAX_AGE,
            self::ID_TOKEN_HINT, self::$validPrompt, self::$uiLocales);
    }

    public function testAuthorizationRequestCreationWithoutMaxAge()
    {
        $stateSerializerObserver = $this->mockStateSerializer(self::STATE);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        list($uri, $serializedState, $nonce) = $authorizationRequestBuilder
            ->setScope(self::$validScope)
            ->setRedirectUri(self::REDIRECT_URI)
            ->setState(self::STATE)
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setPrompt(self::$validPrompt)
            ->setUiLocales(self::$uiLocales)
            ->buildUri();

        $this->assertAuthorizationRequestUriValid($uri, self::$validScope, self::SERIALIZED_STATE, $serializedState, $nonce, null, self::ID_TOKEN_HINT,
            self::$validPrompt, self::$uiLocales);
    }

    public function testAuthorizationRequestCreationWithoutIdTokenHint()
    {
        $stateSerializerObserver = $this->mockStateSerializer(self::STATE);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        list($uri, $serializedState, $nonce) = $authorizationRequestBuilder
            ->setScope(self::$validScope)
            ->setRedirectUri(self::REDIRECT_URI)
            ->setState(self::STATE)
            ->setMaxAge(self::MAX_AGE)
            ->setPrompt(self::$validPrompt)
            ->setUiLocales(self::$uiLocales)
            ->buildUri();
        $this->assertAuthorizationRequestUriValid($uri, self::$validScope, self::SERIALIZED_STATE, $serializedState, $nonce, self::MAX_AGE, null,
            self::$validPrompt, self::$uiLocales);
    }

    public function testAuthorizationRequestCreationWithoutPrompt()
    {
        $stateSerializerObserver = $this->mockStateSerializer(self::STATE);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        list($uri, $serializedState, $nonce) = $authorizationRequestBuilder
            ->setScope(self::$validScope)
            ->setRedirectUri(self::REDIRECT_URI)
            ->setState(self::STATE)
            ->setMaxAge(self::MAX_AGE)
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setUiLocales(self::$uiLocales)
            ->buildUri();
        $this->assertAuthorizationRequestUriValid($uri, self::$validScope, self::SERIALIZED_STATE, $serializedState, $nonce, self::MAX_AGE,
            self::ID_TOKEN_HINT, null, self::$uiLocales);
    }

    public function testAuthorizationRequestCreationWithoutUiLocales()
    {
        $stateSerializerObserver = $this->mockStateSerializer(self::STATE);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        list($uri, $serializedState, $nonce) = $authorizationRequestBuilder
            ->setScope(self::$validScope)
            ->setRedirectUri(self::REDIRECT_URI)
            ->setState(self::STATE)
            ->setMaxAge(self::MAX_AGE)
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setPrompt(self::$validPrompt)
            ->buildUri();

        $this->assertAuthorizationRequestUriValid($uri, self::$validScope, self::SERIALIZED_STATE, $serializedState, $nonce, self::MAX_AGE,
            self::ID_TOKEN_HINT, self::$validPrompt, null);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testAuthorizationRequestCreationWithWrongPrompt()
    {
        $stateSerializerObserver = $this->mockStateSerializer(self::STATE);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        $authorizationRequestBuilder
            ->setScope(self::$validScope)
            ->setRedirectUri(self::REDIRECT_URI)
            ->setState(self::STATE)
            ->setMaxAge(self::MAX_AGE)
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            // NONE + LOGIN => error!
            ->setPrompt(array(PromptValue::NONE, PromptValue::LOGIN))
            ->setUiLocales(self::$uiLocales)
            ->buildUri();
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testAuthorizationRequestCreationWithoutRedirectUri()
    {
        $stateSerializerObserver = $this->mockStateSerializer(self::STATE);
        $authorizationRequestBuilder = new AuthorizationRequestBuilder($stateSerializerObserver, self::AUTHORIZATION_ENDPOINT, self::CLIENT_ID);
        $authorizationRequestBuilder
            ->setScope(self::$validScope)
            ->setState(self::STATE)
            ->setMaxAge(self::MAX_AGE)
            ->setIdTokenHint(self::ID_TOKEN_HINT)
            ->setPrompt(self::$validPrompt)
            ->setUiLocales(self::$uiLocales)
            ->buildUri();
    }

    private function assertAuthorizationRequestUriValid($uri, $expectedScope, $expectedState, $serializedState, $nonce,
                                                        $maxAge, $idTokenHint, $prompt, $uiLocales)
    {
        $parsedUri = parse_url($uri);

        // Check the base uri
        $this->assertEquals(self::AUTHORIZATION_ENDPOINT . '/', $parsedUri['scheme'] . '://' . $parsedUri['host'] . $parsedUri['path']);

        // Check query parameters
        $queryParams = array();
        parse_str($parsedUri['query'], $queryParams);
        $this->assertEquals(implode(' ', $expectedScope), $queryParams[AuthorizationRequestParameter::SCOPE]);
        $this->assertEquals('code', $queryParams[AuthorizationRequestParameter::RESPONSE_TYPE]);
        $this->assertEquals(self::CLIENT_ID, $queryParams[AuthorizationRequestParameter::CLIENT_ID]);
        $this->assertEquals(self::REDIRECT_URI, $queryParams[AuthorizationRequestParameter::REDIRECT_URI]);
        $this->assertEquals($serializedState, $queryParams[AuthorizationRequestParameter::STATE]);
        $this->assertEquals($nonce, $queryParams[AuthorizationRequestParameter::NONCE]);
        if ($maxAge) {
            $this->assertEquals($maxAge, $queryParams[AuthorizationRequestParameter::MAX_AGE]);
        } else {
            $this->assertTrue(!isset($queryParams[AuthorizationRequestParameter::MAX_AGE]) ||
                !$queryParams[AuthorizationRequestParameter::MAX_AGE]);
        }
        if ($idTokenHint) {
            $this->assertEquals($idTokenHint, $queryParams[AuthorizationRequestParameter::ID_TOKEN_HINT]);
        } else {
            $this->assertTrue(!isset($queryParams[AuthorizationRequestParameter::ID_TOKEN_HINT]) ||
                !$queryParams[AuthorizationRequestParameter::ID_TOKEN_HINT]);
        }
        if ($prompt) {
            $this->assertEquals(implode(' ', $prompt), $queryParams[AuthorizationRequestParameter::PROMPT]);
        } else {
            $this->assertTrue(!isset($queryParams[AuthorizationRequestParameter::PROMPT]) ||
                !$queryParams[AuthorizationRequestParameter::PROMPT]);
        }
        if ($uiLocales) {
            $this->assertEquals(implode(' ', $uiLocales), $queryParams[AuthorizationRequestParameter::UI_LOCALES]);
        } else {
            $this->assertTrue(!isset($queryParams[AuthorizationRequestParameter::UI_LOCALES]) ||
                !$queryParams[AuthorizationRequestParameter::UI_LOCALES]);
        }
        $this->assertNotEmpty($nonce);
        $this->assertNotEmpty($serializedState);
        $this->assertEquals($expectedState, $serializedState);
    }

    private function mockStateSerializer($state)
    {
        $stateSerializerObserver = $this->getMock('\PoleNumerique\Oasis\Authz\StateSerializer', array('serialize'));
        if ($state === self::STATE) {
            $stateSerializerObserver->expects($this->any())
                ->method('serialize')
                ->with(self::STATE, $this->anything())
                ->willReturn(self::SERIALIZED_STATE);
        } else {
            $stateSerializerObserver->expects($this->any())
                ->method('serialize')
                ->with($state, $this->anything())
                ->willReturn(self::DEFAULT_SERIALIZED_STATE);
        }
        return $stateSerializerObserver;
    }
}