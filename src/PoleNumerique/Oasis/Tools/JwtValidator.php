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

namespace PoleNumerique\Oasis\Tools;

use Pekkis\Clock\ClockProvider;
use PoleNumerique\Oasis\Exception\OasisException;

abstract class JwtValidator
{
    const DEFAULT_EXCEPTION_PREFIX = 'JWT not valid: ';
    const ACCEPTABLE_CLOCK_SKEW = 10;

    private $jwtVerifier;
    private $exceptionPrefix;

    function __construct(JwtVerifier $jwtVerifier, $exceptionPrefix = self::DEFAULT_EXCEPTION_PREFIX)
    {
        $this->jwtVerifier = $jwtVerifier;
        $this->exceptionPrefix = $exceptionPrefix;
    }

    public function getValidatedJwt($encodedJwt, $clientId, $expectedIssuer)
    {
        $claimset = $this->decodeAndVerifyJwt($encodedJwt);
        $jwt = $this->claimsetToJwt($claimset, $encodedJwt);

        // Various checks
        if ($jwt->getIssuer()) {
            $this->checkIssuer($jwt, $expectedIssuer);
        }
        if ($jwt->getAudience()) {
            $this->checkAudience($jwt, $clientId);
        }
        if ($jwt->getExpirationTime()) {
            $this->checkExpirationTime($jwt);
        }
        if ($jwt->getIssuedAtTime()) {
            $this->checkIssuedAtTime($jwt);
        }
        return $jwt;
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkIssuer(Jwt $jwt, $expectedIssuer)
    {
        if ($jwt->getIssuer() !== $expectedIssuer) {
            throw new OasisException($this->exceptionPrefix . 'issuer claim does not equal the expected issuer.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkAudience(Jwt $jwt, $clientId)
    {
        if (!in_array($clientId, $jwt->getAudience())) {
            throw new OasisException($this->exceptionPrefix . 'audience claim does not contain the client id.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkExpirationTime(Jwt $jwt)
    {
        // Do not use directly "time()" to make tests easier
        if ($jwt->getExpirationTime() < ClockProvider::getClock()->getTime() - self::ACCEPTABLE_CLOCK_SKEW) {
            throw new OasisException($this->exceptionPrefix . 'an expired JWT should not be trusted.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkIssuedAtTime(Jwt $jwt)
    {
        // Do not use directly "time()" to make tests easier
        if ($jwt->getIssuedAtTime() > ClockProvider::getClock()->getTime() + self::ACCEPTABLE_CLOCK_SKEW) {
            throw new OasisException($this->exceptionPrefix . 'a JWT issued after its processing should not be trusted.');
        }
    }

    /**
     * @throws \PoleNumerique\Oasis\Exception\OasisException
     */
    protected function checkClaimPresence(Jwt $jwt, $claim) {
        if (!$jwt->getClaim($claim)) {
            throw new OasisException($this->exceptionPrefix . 'claim "' . $claim . '" is required.');
        }
    }

    protected function decodeAndVerifyJwt($jwt)
    {
        try {
            return $this->jwtVerifier->decodeAndVerifySignature($jwt);
        } catch (\JOSE_Exception $e) {
            throw new OasisException($this->exceptionPrefix . 'the JWT cannot be decoded or its signature is not valid.', $e);
        }
    }

    protected function claimsetToJwt(array $claimset, $encodedJwt)
    {
        return new Jwt($claimset);
    }
} 