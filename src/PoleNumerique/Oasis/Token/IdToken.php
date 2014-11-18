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

class IdToken
{
    private $claimset;
    private $code;

    public function __construct($claimset, $code)
    {
        $this->claimset = $claimset;
        $this->code = $code;
    }

    public function isAppAdmin()
    {
        return boolval($this->getClaim('app_admin'));
    }

    public function isAppUser()
    {
        return boolval($this->getClaim('app_user'));
    }

    public function getAudience()
    {
        $audience = $this->getClaim('aud');
        if ($audience === null) {
            return array();
        }
        if (is_string($audience)) {
            return array($audience);
        }
        // It should either be an array or null value
        return $audience;
    }

    public function getAuthorizationTime()
    {
        return $this->getClaim('auth_time');
    }

    public function getAuthorizedParty()
    {
        return $this->getClaim('azp');
    }

    public function getClaim($key)
    {
        if (!isset($this->claimset[$key])) {
            return null;
        }
        return $this->claimset[$key];
    }

    /**
     * @return string Encoded ID Token
     */
    public function getCode()
    {
        return $this->code;
    }

    public function getExpirationTime()
    {
        return $this->getClaim('exp');
    }

    public function getIssuedAtTime()
    {
        return $this->getClaim('iat');
    }

    public function getIssuer()
    {
        return $this->getClaim('iss');
    }

    public function getNonce()
    {
        return $this->getClaim('nonce');
    }

    public function getSubject()
    {
        return $this->getClaim('sub');
    }
}