# civic-sip-php
**Beware! The library is in development. Stay tuned for stable release announcement.**

Civic [Secure Identity Platform (SIP)](https://www.civic.com/secure-identity-platform) API client implemented in PHP.

## Requirements
PHP >= 7.1

## Installation
```sh
$ composer require blockvis/civic-sip-php
```
## Usage
Please refer [Civic API documentation](https://docs.civic.com/api/index.html) for detailed integration instructions.
```php
use Blockvis\Civic\Sip\AppConfig;
use Blockvis\Civic\Sip\Client;

// Configure Civic App credentials.
$config = new AppConfig(
    CIVIC_APP_ID,
    CIVIC_APP_SECRET,
    CIVIC_APP_PRIVATE_KEY
);

// Instantiate Civic API client with config and HTTP client.
$sipClient = new Client($config, new \GuzzleHttp\Client());

// Exchange Civic JWT for requested user data.
$userData = $sipClient->exchangeToken($jwtToken);
```

An example of returned UserData value object:
```
UserData {
  -userId: "36a59d10-6c53-17f6-9185-gthyte22647a"
  -items: array:2 [
    0 => UserDataItem {
      -label: "contact.personal.email"
      -value: "user.test@gmail.com"
      -isValid: true
      -isOwner: true
    }
    1 => UserDataItem {
      -label: "contact.personal.phoneNumber"
      -value: "+1 555-618-7380"
      -isValid: true
      -isOwner: true
    }
  ]
}
```
## License
The software licensed under the [MIT license](LICENSE).