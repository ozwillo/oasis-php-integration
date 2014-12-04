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

namespace PoleNumerique\Oasis\UserInfo;

class UserInfoValidatorTest extends \PHPUnit_Framework_TestCase
{
    const ISSUER = 'Karl';
    const CLIENT_ID = 'Lenny';

    public function testValidIdTokenValidation()
    {
        $userInfoValidator = $this->provideUserInfoValidator(self::CLIENT_ID, self::ISSUER);
        $userInfoValidator->getValidatedUserInfo('some fake user info jwt', self::CLIENT_ID, self::ISSUER);
        // If the test goes here, it means the test succeed!
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenWithWrongIssuer()
    {
        $userInfoValidator = $this->provideUserInfoValidator(self::CLIENT_ID, self::ISSUER);
        $userInfoValidator->getValidatedUserInfo('Oh, dear God. Can\'t this town go one day without a riot?', self::CLIENT_ID, 'Joe Quimby');
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testIdTokenWithWrongClientId()
    {
        $userInfoValidator = $this->provideUserInfoValidator(self::CLIENT_ID, self::ISSUER);
        $userInfoValidator->getValidatedUserInfo('See ya, cops!', 'Snake Jailbird', self::ISSUER);
    }

    private function provideUserInfoValidator($clientId, $issuer)
    {
        $jwt = array(
            'iss' => $issuer,
            'aud' => $clientId
        );
        $jwtVerifierObserver = $this->getMock(
            '\PoleNumerique\Oasis\Tools\JwtVerifier',
            array('decodeAndVerifySignature'),
            array(),
            'JwtVerifier_test',
            false
        );
        $jwtVerifierObserver->expects($this->any())
            ->method('decodeAndVerifySignature')
            ->with($this->anything())
            ->willReturn($jwt);
        return new UserInfoValidator($jwtVerifierObserver);
    }
}