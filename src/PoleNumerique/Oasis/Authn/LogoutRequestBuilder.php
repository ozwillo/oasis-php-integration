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

use PoleNumerique\Oasis\Exception\OasisException;
use PoleNumerique\Oasis\Token\IdToken;

class LogoutRequestBuilder
{
    private $endSessionEndpoint;

    private $idTokenHint;
    private $postLogoutRedirectUri;
    private $state;

    public function __construct($endSessionEndpoint, $defaultPostLogoutRedirecturi = null)
    {
        $this->endSessionEndpoint = $endSessionEndpoint;

        $this->postLogoutRedirectUri = $defaultPostLogoutRedirecturi;
    }

    public function setIdTokenHint($idToken)
    {
        $encodedIdToken = $idToken;
        if ($idToken instanceof IdToken) {
            $encodedIdToken = $idToken->getCode();
        }
        $this->idTokenHint = $encodedIdToken;
        return $this;
    }

    public function setPostLogoutRedirectUri($postLogoutRedirectUri)
    {
        $this->postLogoutRedirectUri = $postLogoutRedirectUri;
        return $this;
    }

    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    public function buildUri()
    {
        if (!$this->idTokenHint) {
            throw new OasisException('Missing id_token_hint parameter');
        }

        return http_build_url($this->endSessionEndpoint, array(
            'query' => http_build_query(array(
                LogoutRequestParameter::ID_TOKEN_HINT => $this->idTokenHint,
                LogoutRequestParameter::POST_LOGOUT_REDIRECT_URI => $this->postLogoutRedirectUri,
                LogoutRequestParameter::STATE => $this->state
            ))
        ));
    }
}