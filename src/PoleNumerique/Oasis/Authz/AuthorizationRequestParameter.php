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

namespace PoleNumerique\Oasis\Authz;

class AuthorizationRequestParameter
{
    const SCOPE = 'scope';
    const RESPONSE_TYPE = 'response_type';
    const CLIENT_ID = 'client_id';
    const REDIRECT_URI = 'redirect_uri';
    const NONCE = 'nonce';
    const STATE = 'state';
    const PROMPT = 'prompt';
    const MAX_AGE = 'max_age';
    const ID_TOKEN_HINT = 'id_token_hint';
    const UI_LOCALES = 'ui_locales';
}