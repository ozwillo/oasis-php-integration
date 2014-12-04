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

class HttpResponse
{
    private $body;
    private $headers;
    private $statusCode;

    private function __construct($body, $headers, $httpCode)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->statusCode = $httpCode;
    }

    public static function fromCurlResponse($body, $headers, $responseInfo)
    {
        return new HttpResponse($body, $headers, intval($responseInfo['http_code']));
    }

    public function getRawBody()
    {
        return $this->body;
    }

    public function toJson()
    {
        return json_decode($this->body, true);
    }

    public function getHeader($headerField)
    {
        $loweredHeaderField = strtolower($headerField);
        if (!isset($this->headers[$loweredHeaderField])) {
            return null;
        }
        $headerValue = $this->headers[$loweredHeaderField];
        if (is_array($headerValue)) {
            switch (count($headerValue)) {
                case 0: return null;
                case 1: return $headerValue[0];
                default: return $headerValue;
            }
        }
        return $headerValue;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}