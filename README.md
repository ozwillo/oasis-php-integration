# OASIS PHP integration

Library for integrating PHP applications with Oasis.

## Get started

All start with the configuration of the Oasis instance.
The configuration needs 3 elements:

* the OpenID Provider Configuration Document of the OASIS server,
* the client identifier
* the client password.

For the moment, the only available mean to provide the OpenID Provider Configuration Document to the API is to use a PHP array representing it.

For more information about the OpenID Provider Configuration Document:
https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderConfig

The kernel also needs to have a provided cache implementation for storing some data like Oasis public keys.

The cache needs to be common for all requests, not request scoped cache.

A basic implementation is available for APC Storage.

```php
$oasis = Oasis::builder()
  ->setProviderConfig(array(
    'authorization_endpoint' =>
      'https://oasis.example.org/a/auth',
    'token_endpoint' =>
      'https://oasis.example.com/a/token',
    'userinfo_endpoint' =>
      'https://oasis.example.com/a/userinfo'
  ))
  ->setCredentials('myClientId', 'myClientPassword')
  // Optional: use only if you have a single 'redirect_uri'
  ->setDefaultRedirectUri('https://app.example.com/redirect/')
  ->setCache(new ApcCache()) // Might be changed
  ->build();
```

## OAuth 2 + OpenID Connect API

### Get the Authorization request URI

The first step to authenticate someone and get authorizations is to create the Authorization request URI.

```php
list($authorizationRequestUri, $serializedState, $nonce) = $oasis
  ->initAuthorizationRequest()
  // Optional if you only want the 'openid' scope (the default value).
  ->setScope(array('openid', 'profile'))
  // Optional if default 'redirect_uri'
  // Will override the default one if provided
  ->setRedirectUri('https://app2.example.com/another-redirect')
  // Optional: Can be used in the second step to, e.g., redirect
  // your client to the right service / page
  // This API will add a secure random automatically
  // (even if you don't provide this parameter)
  ->setState('project.1.edit')
  ->buildUri();
```
There are other available parameters. For more information about them: [AuthorizationRequestBuilder.php source file](https://github.com/pole-numerique/oasis-php-integration/blob/master/src/PoleNumerique/Oasis/Authz/AuthorizationRequestBuilder.php)

The `$serializedState` and `$nonce` returned by the function need to be saved into a _session_ linked to the current user, as they are required for the next step.

Now you can redirect the user to this URI.

For more information about the Authorizaton request:
http://openid.net/specs/openid-connect-core-1_0.html#AuthRequest

### Get the access token

After the user logged in and authorized your application (or not), he will be redirected to the `redirect_uri` provided in the Authorization request.

The result is conveyed in the query parameters.

Provide the query string of the actual uri to the API for processing it and get an access token in exchange (if the Authorization process was successful).

```php
try {
  list($accessToken, $refreshToken, $idToken, $state) = $oasis
    ->initExchangeCodeForTokenRequest()
    // The query string of the actual uri used by
    // the Authorization endpoint to redirect the user.
    // An array corresponding to the query string can also
    // be provided instead of the query string.
    ->setAuthorizationResponse($queryString)
    // Required if used in Authorization request
    ->setRedirectUri('https://app2.example.com/another-redirect')
    ->setExpectedState($serializedState)
    ->setExpectedNonce($nonce)
    // Optional, in seconds
    // Indicates the maximum lifetime of the HTTP request
    ->setTimeout(30)
    // Optional, in seconds
    // Indicates the maximum lifetime of the ID Token
    ->setTimeToLive(30)
    ->execute();
} catch (AuthorizationResponseException $e) {
  // The authorization request was not successful.
  // Check the associated error code to know what happened
  echo $e->getError() . ' => ' . $e->getErrorDescription();
} catch (TokenResponseException $e) {
  // The access token request was not successful.
  // Check the associated error code to know what happened
  echo $e->getError() . ' [' . $e->getStatusCode() . '] => '
    . $e->getErrorDescription();
}
// Congratulations! You have your access token!
echo $accessToken->getCode();
// If you want to have the expiration time of your access token
echo $accessToken->getExpiresAt(); // timestamp
// The user may authorize more than the requested scope
// To get the authorized scope:
echo $accessToken->getScope();

// You may also user the $idToken object
echo $idToken->getCode();
echo $idToken->isAppAdmin();
echo $idToken->isAppUser();
echo $idToken->getSubject();
// And so on...
```

You may store the received ID Token to use it in Authorization requests with the `id_token_hint` parameter.

This ID Token is already verified so it's not useful to verify it on your own.

The received state is the one you provided in the previous Authorization request.

For more information about Token request:
http://openid.net/specs/openid-connect-core-1_0.html#TokenRequest

For more information about Authorization errors:
https://openid.net/specs/openid-connect-core-1_0.html#AuthError

For more information about Token errors:
https://openid.net/specs/openid-connect-core-1_0.html#TokenErrorResponse

### Get information about the user

Thanks to the access token, you can now get some information about the user (depending on the access token's scopes).
```php
try {
  $userInfo = $oasis
    ->initUserInfoRequest()
    ->setAccessToken($accessToken)
    ->getUserInfo();
} catch (OAuthException $e) {
  // Your access token is not valid (probably because it is expired)
  // You may try to get a new one by redirecting the user to
  // the Authorization endpoint again

  // If you want more information about the error:
  echo $e->getError() . ' [' . $e->getStatusCode() . ']';
}

// Congratulations again! Time to look at information you get.
echo $userInfo->getId();
echo $userInfo->getName();
// and a lot of other interesting data!
```
If you want the full list of available data: [`UserInfo.php` source file](https://github.com/pole-numerique/oasis-php-integration/blob/master/src/PoleNumerique/Oasis/UserInfo/UserInfo.php)


## HOW TO
### Create your own cache implementation

For example, if you want to wrap a cache from doctrine/cache:

```php
class DoctrineCache implements Cache
{
    private $cache;

    function __construct(\Doctrine\Common\Cache\Cache $cache)
    {
        $this->cache = $cache;
    }

    public function get($key)
    {
        return $this->cache->fetch($key);
    }

    public function put($key, $value, $expiresIn = 0)
    {
        $this->cache->save($key, $value, $expiresIn);
    }
}
```

## License

This library is provided under LGPL v3.

```
Copyright (C) 2014 Atol Conseils et Développements

oasis-php-integration is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

oasis-php-integration is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
```
