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

class Address
{
    private $adressData;

    function __construct(array $adressData)
    {
        $this->adressData = $adressData;
    }

    public function getFullAddress()
    {
        return $this->getAddressData('full_address');
    }

    public function getStreetAddress()
    {
        return $this->getAddressData('street_address');
    }

    public function getLocality()
    {
        return $this->getAddressData('locality');
    }

    public function getRegion()
    {
        return $this->getAddressData('region');
    }

    public function getPostalCode()
    {
        return $this->getAddressData('postal_code');
    }

    public function getCountry()
    {
        return $this->getAddressData('country');
    }

    private function getAddressData($key)
    {
        if (!isset($this->adressData[$key])) {
            return null;
        }
        return $this->adressData[$key];
    }
}