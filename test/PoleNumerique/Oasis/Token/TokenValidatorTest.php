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

class TokenValidatorTest extends \PHPUnit_Framework_TestCase
{
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
        ClockProvider::setClock(new FixedClock($this->currentTime - TokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $tokenValidator = $this->provideTokenValidator(self::CLIENT_ID, self::NONCE, self::ISSUER);
        $idToken = $tokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);

        $this->assertEquals(self::ENCODED_ID_TOKEN, $idToken->getCode());
        $this->assertEquals(self::ISSUER, $idToken->getIssuer());
        $this->assertEquals(array(self::CLIENT_ID), $idToken->getAudience());
        $this->assertEquals(self::NONCE, $idToken->getNonce());
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testExpiredIdToken()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime + TokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $tokenValidator = $this->provideTokenValidator(self::CLIENT_ID, self::NONCE, self::ISSUER);
        $tokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenWithWrongIssuer()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime - TokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $tokenValidator = $this->provideTokenValidator(self::CLIENT_ID, self::NONCE, 'Springfield school');
        $tokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenWithWrongClientId()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime - TokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $tokenValidator = $this->provideTokenValidator('El Barto', self::NONCE, self::ISSUER);
        $tokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenWithWrongNonce()
    {
        ClockProvider::setClock(new FixedClock($this->currentTime - TokenValidator::ACCEPTABLE_CLOCK_SKEW * 2));
        $tokenValidator = $this->provideTokenValidator(self::CLIENT_ID, 'Doh', self::ISSUER);
        $tokenValidator->validateAndGetIdToken(self::ENCODED_ID_TOKEN, self::CLIENT_ID, self::ISSUER, self::NONCE);
    }

    private function provideTokenValidator($clientId, $nonce, $issuer)
    {
        $jwt = array(
            'iss' => $issuer,
            'aud' => $clientId,
            'exp' => $this->currentTime,
            'nonce' => $nonce
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
        return new TokenValidator($jwtVerifierObserver);
    }
}