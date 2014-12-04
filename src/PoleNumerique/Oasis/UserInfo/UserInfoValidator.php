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

use PoleNumerique\Oasis\Tools\JwtValidator;

class UserInfoValidator extends JwtValidator
{
    public function getValidatedUserInfo($encodedUserInfo, $clientId, $expectedIssuer)
    {
        return $this->getValidatedJwt($encodedUserInfo, $clientId, $expectedIssuer);
    }

    protected function claimsetToJwt(array $claimset, $encodedJwt)
    {
        return new UserInfo($claimset);
    }
}