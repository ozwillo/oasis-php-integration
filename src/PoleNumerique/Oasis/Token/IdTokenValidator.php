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

use PoleNumerique\Oasis\Exception\OasisException;
use PoleNumerique\Oasis\Tools\JwtClaims;
use PoleNumerique\Oasis\Tools\JwtValidator;
use PoleNumerique\Oasis\Tools\JwtVerifier;

class IdTokenValidator extends JwtValidator
{
    const ACCEPTABLE_CLOCK_SKEW = 10;
    const EXCEPTION_PREFIX = 'Id Token not valid: ';

    function __construct(JwtVerifier $jwtVerifier)
    {
        parent::__construct($jwtVerifier, self::EXCEPTION_PREFIX);
    }

    /**
     * Throws an exception if the ID Token is not valid
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
     *
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    public function validateAndGetIdToken($encodedIdToken, $clientId, $expectedIssuer, $expectedNonce)
    {
        $idToken = $this->getValidatedJwt($encodedIdToken, $clientId, $expectedIssuer);
        $this->checkNonce($idToken, $expectedNonce);
        $this->checkClaimPresence($idToken, JwtClaims::ISSUER);
        $this->checkClaimPresence($idToken, JwtClaims::SUBJECT);
        $this->checkClaimPresence($idToken, JwtClaims::AUDIENCE);
        $this->checkClaimPresence($idToken, JwtClaims::EXPIRATION_TIME);
        $this->checkClaimPresence($idToken, JwtClaims::ISSUED_AT);

        return $idToken;
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkAudience(IdToken $idToken, $clientId)
    {
        parent::checkAudience($idToken, $clientId);

        if (count($idToken->getAudience()) > 1 && $idToken->getAuthorizedParty() !== $clientId) {
            throw new OasisException(self::EXCEPTION_PREFIX . 'authorized party claim does not equal the client id.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkNonce(IdToken $idToken, $expectedNonce)
    {
        if ($idToken->getNonce() !== $expectedNonce) {
            throw new OasisException(self::EXCEPTION_PREFIX . 'expected and received nonces are different.');
        }
    }

    protected function claimsetToJwt(array $claimset, $encodedJwt)
    {
        return new IdToken($claimset, $encodedJwt);
    }
}