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

interface Cache
{
    /**
     * Return the value associated with the key.
     * <p>
     * If there is no value associated to the key or if the data is expired, it will return NULL.
     */
    public function get($key);

    /**
     * Put a value for a defined key into the cache.
     * <p>
     * Must expires in $expiresIn seconds.
     * <p>
     * $expiresIn = 0 means that the value will never expires
     */
    public function put($key, $value, $expiresIn = 0);
}