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

use PoleNumerique\Oasis\Token\AccessToken;
use PoleNumerique\Oasis\Tools\HttpResponse;

class UserInfoRequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    const USERINFO_ENDPOINT = 'http://oasis.example.org/a/userinfo';
    const CLIENT_ID = 'Cletus';
    const ISSUER = 'Brandine';
    const ACCESS_TOKEN_CODE = 'SSB3YXMgYmVhdCBpbiB0aWMtdGFjLXRvZSBieSBhIGNoaWNrZW4=';
    const SERIALIZED_JWT = 'UnViZWxsYSwgd2UgZ290IHlvdSBhIG1pZGRsZSBuYW1lIQ==';

    private static $userInfoData = array(
        'name' => 'Cletus Spuckler',
        'family_name' => 'Spuckler',
        'given_name' => 'Cletus Del Roy',
        'address' => array(
            'locality' => 'Near Springfield'
        )
    );

    private $accessToken;

    function __construct()
    {
        // Only access token code parameter is necessary here, the others are not useful
        $this->accessToken = new AccessToken(self::ACCESS_TOKEN_CODE, array(), 3000, time());
    }


    public function testValidUserInfoRequest()
    {
        $httpClientObserver = $this->provideHttpClient(self::$userInfoData);
        $userInfoValidatorObserver = $this->provideUserInfoValidator(self::$userInfoData);
        $userInfoRequestBuilder = new UserInfoRequestBuilder($httpClientObserver, $userInfoValidatorObserver,
            self::USERINFO_ENDPOINT, self::CLIENT_ID, self::ISSUER);
        $userInfo = $userInfoRequestBuilder->setAccessToken($this->accessToken)
            ->getUserInfo();
        $this->assertUserInfoValid($userInfo);
    }

    /**
     * @expectedException \PoleNumerique\Oasis\Exception\OasisException
     */
    public function testUserInfoRequestWithoutAccessToken()
    {
        $httpClientObserver = $this->provideHttpClient(false);
        $userInfoValidatorObserver = $this->provideUserInfoValidator(false);
        $userInfoRequestBuilder = new UserInfoRequestBuilder($httpClientObserver, $userInfoValidatorObserver,
            self::USERINFO_ENDPOINT, self::CLIENT_ID, self::ISSUER);
        $userInfoRequestBuilder->getUserInfo();
    }

    private function assertUserInfoValid(UserInfo $userInfo)
    {
        $this->assertEquals(self::$userInfoData['name'], $userInfo->getName());
        $this->assertEquals(self::$userInfoData['family_name'], $userInfo->getFamilyName());
        $this->assertEquals(self::$userInfoData['given_name'], $userInfo->getGivenName());
        $this->assertEquals(self::$userInfoData['address']['locality'], $userInfo->getAddress()->getLocality());
    }

    private function provideHttpClient($userInfo)
    {
        $httpClientObserver = $this->getMock('\PoleNumerique\Oasis\Tools\HttpClient', array('get'));
        if ($userInfo) {
            $httpResponse = HttpResponse::fromCurlResponse(self::SERIALIZED_JWT, array(), array(
                'http_code' => 200
            ));
            $httpClientObserver->expects($this->once())
                ->method('get')
                ->with(self::USERINFO_ENDPOINT, $this->callback(function ($options) {
                    return strtolower($options['auth']['method']) === 'bearer' &&
                    $options['auth']['token'] === self::ACCESS_TOKEN_CODE;
                }))
                ->willReturn($httpResponse);
        } else {
            $httpClientObserver->expects($this->never())
                ->method('get')
                ->with($this->anything(), $this->anything());
        }
        return $httpClientObserver;
    }

    private function provideUserInfoValidator($userInfoData)
    {
        $userInfoValidatorObserver = $this->getMock(
            '\PoleNumerique\Oasis\UserInfo\UserInfoValidator',
            array('getValidatedUserInfo'),
            array(),
            'UserInfoValidator_test',
            false
        );
        if ($userInfoData) {
            $userInfoValidatorObserver->expects($this->once())
                ->method('getValidatedUserInfo')
                ->with(self::SERIALIZED_JWT, self::CLIENT_ID, self::ISSUER)
                ->willReturn(new UserInfo($userInfoData));
        } else {
            $userInfoValidatorObserver->expects($this->never())
                ->method('getValidatedUserInfo')
                ->with($this->anything(), $this->anything(), $this->anything());
        }
        return $userInfoValidatorObserver;
    }
}