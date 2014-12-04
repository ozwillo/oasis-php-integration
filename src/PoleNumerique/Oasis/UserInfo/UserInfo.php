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

use PoleNumerique\Oasis\Tools\Jwt;

class UserInfo extends Jwt
{
    private $userInfoData;

    public function getUserId()
    {
        return $this->getSubject();
    }

    public function getName()
    {
        return $this->getClaim('name');
    }

    public function getFamilyName()
    {
        return $this->getClaim('family_name');
    }

    public function getGivenName()
    {
        return $this->getClaim('given_name');
    }

    public function getMiddleName()
    {
        return $this->getClaim('middle_name');
    }

    public function getNickname()
    {
        return $this->getClaim('nickname');
    }

    public function getPicture()
    {
        return $this->getClaim('picture');
    }

    public function getGender()
    {
        return $this->getClaim('gender');
    }

    public function isFemale()
    {
        return $this->getGender() === 'female';
    }

    public function isMale()
    {
        return $this->getGender() === 'male';
    }

    public function getBirthdate()
    {
        return $this->getClaim('birthdate');
    }

    public function getZoneInfo()
    {
        return $this->getClaim('zoneinfo');
    }

    public function getLocale()
    {
        return $this->getClaim('locale');
    }

    public function getPhoneNumber()
    {
        return $this->getClaim('phone_number');
    }

    public function isPhoneNumberVerifed()
    {
        return boolval($this->getClaim('phone_number_verified'));
    }

    public function getAddress()
    {
        return new Address($this->getClaim('address'));
    }

    public function getUpdatedAt()
    {
        return $this->getClaim('updated_at');
    }

    public function isOrganizationAdmin()
    {
        return boolval($this->getClaim('organization_admin'));
    }

    public function getOrganizationId()
    {
        return $this->getClaim('organization_id');
    }
}