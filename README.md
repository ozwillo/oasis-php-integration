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

```php
$oasis = Oasis::builder()
  ->setProviderConfig(array(
    'authorization_endpoint' =>
      'https://oasis.example.org/a/auth'
  ))
  ->setCredentials('myClientId', 'myClientPassword')
  // Optional: use only if you have a single 'redirect_uri'
  ->setDefaultRedirectUri('https://app.example.com/redirect/')
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