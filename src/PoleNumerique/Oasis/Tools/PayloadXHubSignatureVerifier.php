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

use PoleNumerique\Oasis\Exception\OasisException;

class PayloadXHubSignatureVerifier
{
    /**
     * Expected name of the header in the raw request.
     */
    CONST X_HUB_SIGNATURE_HEADER = 'X-Hub-Signature';
    /**
     * Expected name of the header in the superglobal variable $_SERVER.
     */
    CONST X_HUB_SIGNATURE_SERVER_HEADER = 'HTTP_X_HUB_SIGNATURE';

    CONST SIGNATURE_PREFIX = 'sha1=';

    /**
     * Verify the signature of a request from the Oasis server to your application.
     *
     * @param $xHubSignatureValue string X-Hub-Signature header value of te request
     * @param $payload string Content of the Oasis request
     * @param $secret string Secret used for signing the request
     * @throws \PoleNumerique\Oasis\Exception\OasisException if the signature is not matching the expected one
     */
    public static function verify($xHubSignatureValue, $payload, $secret)
    {
        if (strncmp($xHubSignatureValue, self::SIGNATURE_PREFIX, strlen(self::SIGNATURE_PREFIX)) !== 0) {
            throw new OasisException('Wrong signature prefix.');
        }
        $signature = hash_hmac('sha1', $payload, $secret);
        if (substr_compare($xHubSignatureValue, $signature, strlen(self::SIGNATURE_PREFIX), strlen($signature), true) !== 0) {
            throw new OasisException('Signatures do not match.');
        }
    }
}
