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
use PoleNumerique\Oasis\Exception\HttpException;
use PoleNumerique\Oasis\Exception\OasisException;

class KeysProvider
{
    const KEYS_CACHE_ID = 'keys';
    const KEYS_SET_ID = 'set';
    const DOWNLOADED_AT_ID = 'downloadedAt';
    // XXX: Make it configurable?
    const JWKS_LIFETIME = 86400; // = 24 hours
    const MIN_FRESHNESS_DELAY = 420; // = 7 minutes

    private $cache;
    private $httpClient;
    private $clientId;
    private $clientSecret;
    private $jwksUri;

    function __construct(Cache $cache, HttpClient $httpClient, $clientId, $clientSecret, $jwksUri)
    {
        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->jwksUri = $jwksUri;
    }

    public function getKeyById($kid)
    {
        $keysData = $this->cache->get(self::KEYS_SET_ID);
        if (!$keysData) {
            list($keys, $downloadedAt) = $this->updateCacheAndGetKeys();
        } else {
            $keys = $keysData[self::KEYS_SET_ID];
            $downloadedAt = $keysData[self::DOWNLOADED_AT_ID];
        }

        // Update the cache if the key does not exists and if the cache was not updated for a few minutes
        if (!isset($keys[$kid])) {
            if (ClockProvider::getClock()->getTime() < $downloadedAt + self::MIN_FRESHNESS_DELAY) {
                throw new OasisException('Unknown key id: ' . $kid);
            }
            list($keys, $downloadedAt) = $this->updateCacheAndGetKeys();
            if (!isset($keys[$kid])) {
                throw new OasisException('Unknown key id: ' . $kid);
            }
        }
        return $keys[$kid];
    }

    private function updateCacheAndGetKeys()
    {
        try {
            $httpResponse = $this->httpClient->get($this->jwksUri, array(
                'auth' => array(
                    'method' => HttpClient::AUTH_BASIC,
                    'username' => $this->clientId,
                    'password' => $this->clientSecret
                )
            ));
            if ($httpResponse->getStatusCode() !== 200) {
                throw new OasisException('Error while trying to get OASIS keys [Status=' . $httpResponse->getStatusCode() . ']');
            }
            $keys = $this->jwksToKeyArray($httpResponse->toJson());
            $downloadedAt = ClockProvider::getClock()->getTime();
            $this->cache->put(self::KEYS_CACHE_ID, array(
                self::KEYS_SET_ID => $keys,
                self::DOWNLOADED_AT_ID => $downloadedAt
            ), self::JWKS_LIFETIME);
            return array($keys, $downloadedAt);
        } catch (HttpException $e) {
            throw new OasisException('JWKS URI unreachable', $e);
        }
    }

    private function jwksToKeyArray($jwks)
    {
        $keysById = array();
        foreach ($jwks['keys'] as $key) {
            $keysById[$key['kid']] = \JOSE_JWK::decode($key);
        }
        return $keysById;
    }
}