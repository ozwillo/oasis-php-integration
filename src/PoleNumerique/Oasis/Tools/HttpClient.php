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

namespace PoleNumerique\Oasis\Tools;

use PoleNumerique\Oasis\Exception\HttpException;

class HttpClient
{
    const DEFAULT_TIMEOUT = 60;

    const AUTH_BASIC = 'basic';
    const AUTH_BEARER = 'bearer';

    /**
     * @throws HttpException
     */
    public function get($url, $options)
    {
        if (isset($options['params'])) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($options['params']);
        }
        $curlOptions = $this->getCurlOptions($url, 'GET', $options);
        return $this->build($curlOptions);
    }

    /**
     * @throws \HttpException
     */
    public function post($url, $options)
    {
        $curlOptions = $this->getCurlOptions($url, 'POST', $options);
        if (isset($options['params'])) {
            $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($options['params']);
        }
        return $this->build($curlOptions);
    }

    private function getCurlOptions($url, $method, $options)
    {
        $timeout = isset($options['timeout']) && $options['timeout'] ? $options['timeout'] : self::DEFAULT_TIMEOUT;
        $curlOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => array()
            // XXX: Put our own useragent?
        );
        if (isset($options['auth'])) {
            if ($options['auth']['method'] === self::AUTH_BASIC) {
                $curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
                $curlOptions[CURLOPT_USERPWD] = $options['auth']['username'] . ':' . $options['auth']['password'];
            } else if ($options['auth']['method'] === self::AUTH_BEARER) {
                $curlOptions[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $options['auth']['token'];
            }
        }
        if (isset($options['headers'])) {
            foreach ($options['headers'] as $key => $value)
                $curlOptions[CURLOPT_HTTPHEADER][] = $key . ': ' . $value;
        }
        return $curlOptions;
    }

    /**
     * @throws HttpException
     */
    private function build($curlOptions)
    {
        $curlSession = curl_init();
        curl_setopt_array($curlSession, $curlOptions);

        // Callback which will parse each header line of the response and put it in $headers array
        $firstLine = true;
        $headers = array();
        curl_setopt($curlSession, CURLOPT_HEADERFUNCTION, function ($curl, $headerLine) use (&$firstLine, &$headers) {
            // Do not process the first line (which correspond to "HTTP/1.1 [...]")
            if ($firstLine) {
                $firstLine = false;
                return strlen($headerLine);
            }
            if (!strlen(trim($headerLine))) {
                return strlen($headerLine);
            }
            $parts = explode(':', $headerLine, 2);
            $key = strtolower(trim($parts[0]));
            $value = isset($parts[1]) ? trim($parts[1]) : '';
            if (!isset($headers[$key])) {
                $headers[$key] = array($value);
            } else {
                $headers[$key][] = $value;
            }
            // CURLOPT_HEADERFUNCTION callback needs to get the length of the line
            return strlen($headerLine);
        });

        if (($body = curl_exec($curlSession)) === false) {
            throw new HttpException(curl_error($curlSession));
        }
        $responseInfo = curl_getinfo($curlSession);
        curl_close($curlSession);

        return HttpResponse::fromCurlResponse($body, $headers, $responseInfo);
    }
}