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
use Pekkis\Clock\FixedClock;
use PoleNumerique\Oasis\Tools\JwtClaims;

class IdTokenValidatorTest extends \PHPUnit_Framework_TestCase
{
    const SUBJECT = 'Seymour Skinner';
    const ENCODED_ID_TOKEN = 'I\'m the kid who ate a frog.';
    const ISSUER = '747 Evergreen Terrace';
    const CLIENT_ID = 'Bart Simpson';
    const NONCE = 'Eat my short';

    private $currentTime;

    function __construct()
    {
        $this->currentTime = time();
    }

    public function testValidIdTokenValidation()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime - IdTokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $idTokenValidator = $this->provideIdTokenValidator(self::SUBJECT, self::CLIENT_ID, self::NONCE, self::ISSUER,
            ClockProvider::getClock()->getTime());
        $idToken = $idTokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);

        $this->assertEquals(self::ENCODED_ID_TOKEN, $idToken->getEncoded());
        $this->assertEquals(self::ISSUER, $idToken->getIssuer());
        $this->assertEquals(array(self::CLIENT_ID), $idToken->getAudience());
        $this->assertEquals(self::NONCE, $idToken->getNonce());
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testExpiredIdToken()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime + IdTokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $idTokenValidator = $this->provideIdTokenValidator(self::SUBJECT, self::CLIENT_ID, self::NONCE, self::ISSUER,
            ClockProvider::getClock()->getTime());
        $idTokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenWithWrongIssuer()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime - IdTokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $idTokenValidator = $this->provideIdTokenValidator(self::SUBJECT, self::CLIENT_ID, self::NONCE, 'Springfield school',
            ClockProvider::getClock()->getTime());
        $idTokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenWithWrongClientId()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime - IdTokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $idTokenValidator = $this->provideIdTokenValidator(self::SUBJECT, 'El Barto', self::NONCE, self::ISSUER,
            ClockProvider::getClock()->getTime());
        $idTokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenWithWrongNonce()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime - IdTokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $idTokenValidator = $this->provideIdTokenValidator(self::SUBJECT, self::CLIENT_ID, 'Doh', self::ISSUER,
            ClockProvider::getClock()->getTime());
        $idTokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenIssuedAtAFutureTime()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime - IdTokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $idTokenValidator = $this->provideIdTokenValidator(self::SUBJECT, self::CLIENT_ID, 'Doh', self::ISSUER,
            ClockProvider::getClock()->getTime() + IdTokenValidator::ACCEPTABLE_CLOCK_SKEW * 100);
        $idTokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    private function provideIdTokenValidator($subject, $clientId, $nonce, $issuer, $issuedAtTime)
    {
        $jwt = array(
            JwtClaims::SUBJECT => $subject,
            JwtClaims::ISSUER => $issuer,
            JwtClaims::AUDIENCE => $clientId,
            JwtClaims::EXPIRATION_TIME => $this->currentTime,
            JwtClaims::ISSUED_AT => $issuedAtTime,
            IdTokenClaims::NONCE => $nonce
        );
        $jwtVerifierObserver = $this->getMock(
            '\PoleNumerique\Oasis\Tools\JwtVerifier',
            array('decodeAndVerifySignature'),
            array(),
            'JwtVerifier_test',
            false
        );
        $jwtVerifierObserver->expects($this->once())
            ->method('decodeAndVerifySignature')
            ->with(self::ENCODED_ID_TOKEN)
            ->willReturn($jwt);
        return new IdTokenValidator($jwtVerifierObserver);
    }
}