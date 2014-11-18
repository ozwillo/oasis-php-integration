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

/**
 * Basic implementation of the Cache interface with APC storage.
 * <p>
 * This implementation is mainly an example of how you may implement the Cache interface.
 * Indeed, it does not provide any other method like cache invalidation or stats.
 * Thus, you are invited to make your own cache or to make a wrapper around a Cache library like Doctrine Cache library.
 */
class ApcCache implements Cache
{
    const DEFAULT_PREFIX = 'oasis.';

    private $prefix;

    /**
     * @param string $prefix To avoid conflicts between your application and our API
     */
    function __construct($prefix = self::DEFAULT_PREFIX)
    {
        $this->prefix = $prefix;
    }


    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return apc_fetch($this->prefix . $key);
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $expiresIn = 0)
    {
        apc_store($this->prefix . $key, $value, (int)$expiresIn);
    }
}