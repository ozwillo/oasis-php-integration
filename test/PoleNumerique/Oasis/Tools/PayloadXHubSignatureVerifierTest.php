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

class PayloadXHubSignatureVerifierTest extends \PHPUnit_Framework_TestCase
{
    const SECRET = 'The secret war of Lisa Simpson';
    // The signature has both uppercase and lowercase characters to check that the hash comparison is case insensitive
    const SIGNATURE = 'sha1=0242C516d63BdFA8F465896C79497bb7835CEecC';
    const PAYLOAD = '{"message":"Lisa.enrollTo(\'military school\')","eventType":"Bart.prank"}';

    public function testValidSignatureVerification()
    {
        PayloadXHubSignatureVerifier::verify(self::SIGNATURE, self::PAYLOAD, self::SECRET);
        // If it goes here, it means it's ok
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testSignatureVerificationWithWrongSecret()
    {
        PayloadXHubSignatureVerifier::verify(self::SIGNATURE, self::PAYLOAD, 'The City of New York vs. Homer Simpson');
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testSignatureVerificationWithAlmostRightSignature()
    {
        PayloadXHubSignatureVerifier::verify(
            substr(self::SIGNATURE, 0, -1) . 'G', // Here, the signature ends with the character 'G' and not 'C'
            self::PAYLOAD,
            self::SECRET
        );
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testSignatureWithoutPrefix()
    {
        PayloadXHubSignatureVerifier::verify(substr(self::SIGNATURE, 5 /* = strlen('sha1=') */), self::PAYLOAD, self::SECRET);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testSignatureWithWrongHashFunction()
    {
        $signature = hash_hmac('md5', self::PAYLOAD, self::SECRET);
        PayloadXHubSignatureVerifier::verify('md5=' . $signature, self::PAYLOAD, self::SECRET);
    }
}
