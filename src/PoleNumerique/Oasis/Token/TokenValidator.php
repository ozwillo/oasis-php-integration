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
use PoleNumerique\Oasis\Exception\OasisException;
use PoleNumerique\Oasis\Tools\JwtVerifier;

class TokenValidator
{
    const ACCEPTABLE_CLOCK_SKEW = 10;

    private $jwtVerifier;

    function __construct(JwtVerifier $jwtVerifier)
    {
        $this->jwtVerifier = $jwtVerifier;
    }

    /**
     * Throws an exception if the ID Token is not valid
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
     *
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    public function validateAndGetIdToken($encodedIdToken, $clientId, $expectedIssuer, $expectedNonce)
    {
        $claimset = $this->decodeAndVerifyIdToken($encodedIdToken);
        $idToken = new IdToken($claimset, $encodedIdToken);
        // Various checks
        $this->checkIssuer($idToken, $expectedIssuer);
        $this->checkAudience($idToken, $clientId);
        $this->checkExpirationTime($idToken);
        $this->checkIssuedAtTime($idToken);
        $this->checkNonce($idToken, $expectedNonce);

        return $idToken;
    }

    protected function decodeAndVerifyIdToken($idToken)
    {
        try {
            return $this->jwtVerifier->decodeAndVerifySignature($idToken);
        } catch (\JOSE_Exception $e) {
            throw new OasisException('ID Token not valid.', $e);
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkIssuer(IdToken $idToken, $expectedIssuer)
    {
        if ($idToken->getIssuer() !== $expectedIssuer) {
            throw new OasisException('ID Token not valid: issuer claim does not equal the expected issuer.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkAudience(IdToken $idToken, $clientId)
    {
        $audience = $idToken->getAudience();
        if (!in_array($clientId, $audience)) {
            throw new OasisException('ID Token not valid: audience claim does not contain the client id.');
        }
        if (count($audience) > 1 && $idToken->getAuthorizedParty() !== $clientId) {
            throw new OasisException('ID Token not valid: authorized party claim does not equal the client id.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkExpirationTime(IdToken $idToken)
    {
        // Do not use directly "time()" to make tests easier
        if ($idToken->getExpirationTime() < ClockProvider::getClock()->getTime() - self::ACCEPTABLE_CLOCK_SKEW) {
            throw new OasisException('ID Token not valid: an expired ID Token should not be trusted.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkIssuedAtTime(IdToken $idToken)
    {
        // Do not use directly "time()" to make tests easier
        if ($idToken->getIssuedAtTime() > ClockProvider::getClock()->getTime() + self::ACCEPTABLE_CLOCK_SKEW) {
            throw new OasisException('ID Token not valid: an ID Token issued after its processing should not be trusted.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkNonce(IdToken $idToken, $expectedNonce)
    {
        if ($idToken->getNonce() !== $expectedNonce) {
            throw new OasisException('ID Token not valid: expected and received nonces are different.');
        }
    }
}