# civic-sip-php

Civic [Secure Identity Platform (SIP)](https://www.civic.com/secure-identity-platform) API client implemented in PHP.

## What is Civic
Civic is the next generation blockchain based secure identity management platform. 
It allows to authenticate users without the need for traditional physical IDs,
knowledge based authentication, username/password, and two-factor hardware tokens.
Civic's Secure Identity Platform (SIP) uses a verified identity for multi-factor authentication
on web and mobile apps without the need for usernames or passwords.
The SIP provides partners with functionality such as:
* secure public or private 2FA user login,
* onboarding of verified users with customized flows.

## How Civic works
An individual downloads the Civic App and completes an Identity Validation Process customized to the Civic Business Customer requirements. 
This process verifies Personally Identifiable Information (PII) to ensure ownership of the identity with enough data to establish the level of trust required by the Civic Business Customer.
In other words, more PII may be collected to establish a high level of trust,
e.g. scanning of passport, driver’s license and social security number, while only minimal PII, e.g. only email and mobile phone number, may be collected for new users when the Civic Business Customer only wants to verify the user is real and unique.

After validation, the user is now considered a Civic Member with authenticated **identity data secured in the Civic App on the user’s device, not stored by Civic.** 
The Civic Member may share this previously authenticated identity data with Civic Business Customers, businesses that enter into a partnership with Civic which leverage blockchain technology for real-time authentication of Civic Member identity data.

**Ultimately Civic Members have full control over their identity data and ability to choose what and who to share with.**

## Getting Started
In order to integrate your application with Civic you need to do the following:

#### 1. **Obtain application credentials (keys)**
Go to [Civic Partner Portal](https://sip-partners.civic.com). If you already registered as Civic partner you may proceed with application registration (option B) otherwise you will be asked for some company details to complete the partner registration process.
  * (A) To become a partner you will need to provide Company Name, Primary Domain, Primary Contact and accept Developer Terms of Service.
  **Important: To prove that you are eligible to create an account for a company, Civic requires you to use your company email address when signing up to Civic and enforces that your email domain matches the primary domain of your company during registration.**
    
  * (B) Click "New Application" button and fill in the Application Form (Application Name, Whitelisted Domains, Full URL to logo image file). After submitting the form you will be provided with application credentials: `App ID, Signing keypair, App Secret, Encryption keypair`. This credentials will be used later on to configure Civic SIP client.

#### 2. Install civic-sip-php package
```sh
$ composer require blockvis/civic-sip-php
```
#### 3. Implement User-Agent (Browser) functionality
Include the `civic.sip.js` script on your page. This exposes a single global object, civic.
```html
<link rel="stylesheet" href="https://hosted-sip.civic.com/css/civic-modal.css">

<script src="https://hosted-sip.civic.com/js/civic.sip.min.js"></script>
```
Instantiate instance of `civic.sip`.
```javascript
var civicSip = new civic.sip({ appId: 'Your Application ID' });
```
Implement event handlers.
```javascript
// Start scope request
$('button.js-signup').click(function(event) {
    civicSip.signup({ style: 'popup', scopeRequest: civicSip.ScopeRequests.BASIC_SIGNUP });
});

// Listen for data
civicSip.on('auth-code-received', function (event) {
    /*
        event:
        {
            event: "scoperequest:auth-code-received",
            response: "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJqdGkiOiI2Y2EwNTEzMi0wYTJmLTQwZjItYTg2Yi03NTkwYmRjYzBmZmUiLCJpYXQiOjE0OTQyMjUxMTkuMTk4LCJleHAiOjE0OTQyMjUyOTkuMTk4LCJpc3MiOiJjaXZpYy1zaXAtaG9zdGVkLXNlcnZpY2UiLCJhdWQiOiJodHRwczovL3BoNHg1ODA4MTUuZXhlY3V0ZS1hcGkudXMtZWFzdC0xLmFtYXpvbmF3cy5jb20vZGV2Iiwic3ViIjoiY2l2aWMtc2lwLWhvc3RlZC1zZXJ2aWNlIiwiZGF0YSI6eyJjb2RlVG9rZW4iOiJjY2E3NTE1Ni0wNTY2LTRhNjUtYWZkMi1iOTQzNjc1NDY5NGIifX0.gUGzPPI2Av43t1kVg35diCm4VF9RUCF5d4hfQhcSLFvKC69RamVDYHxPvofyyoTlwZZaX5QI7ATiEMcJOjXRYQ",
            type: "code"
        }
    */

    // Encoded JWT Token is sent to the server
    const jwtToken = event.response;

    // Your function to pass JWT token to your server
    sendAuthCode(jwtToken);
});

civicSip.on('user-cancelled', function (event) {
    /*
        event:
        {
          event: "scoperequest:user-cancelled"
        }
    */
});

// Error events
civicSip.on('civic-sip-error', function (error) {
    // handle error display if necessary.
    console.log('Error type = ' + error.type);
    console.log('Error message = ' + error.message);
});
```

#### 4. Implement Application server functionality
Use `Blockvis\Civic\Sip\Client` with your application credentials to exchange the authorization code `$jwtToken` sent from user browser for requested user data. 
```php
use Blockvis\Civic\Sip\AppConfig;
use Blockvis\Civic\Sip\Client;

// Configure Civic App credentials.
$config = new AppConfig(
    'Your Application ID',
    'Your Application Secret',
    'Your Application Private Signing Key'
);

// Instantiate Civic API client with config and HTTP client.
$sipClient = new Client($config, new \GuzzleHttp\Client());

// Exchange Civic authorization code for requested user data.
$userData = $sipClient->exchangeToken($jwtToken);
```

An example of returned `UserData` value object:
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

You can iterate through the data items as following:

```php
foreach ($userData->items() as $dataItem) {
    echo $dataItem->label() . ' = ' . $dataItem->value() . PHP_EOL;
}
``` 

You can also access individual data items by their labels. Note that `userId` is a property of `UsedData` object and not included in data items array.

```php
$userId = $userData->userId();
$email = $userData->getByLabel('contact.personal.email')->value();
$phone = $userData->getByLabel('contact.personal.phoneNumber')->value();
```

## Civic integration
Currently only Civic Hosted option is available for partners integration. This is the simplest route to integration as it provides a flow similar to the traditional oAuth2 authorization code flow, with Civic performing the role of the Authorization server.
This option delivers a secure solution and minimises server side development required by the partner.

The following user signup example explains the general Civic Hosted option codeflow.
![Civic Code Flow](http://docs.civic.com/images/codeflow.png)

1. **Signup.** The user clicks “Signup with Civic” button on your website page. The event handler calls a method in the Civic JS library to initiate signup.

2. **Launch Popup.** A modal is displayed which contains an iframe to house the QR code. A request is made to the Civic server to generate a QR code for your scope request.

3. **QR Code.** The server checks that the domain for the parent document of the iframe corresponds to the domain white list set in the partner account before serving the code. The QR code bridges the air gap between the browser and the user’s smart phone.

4. **Scan.** The user scans the QR code using the Civic mobile app and is prompted to authorize or deny the scope request. The prompt highlights the data that is being requested and the requesting party.

5. **Grant Request.** Upon granting the request, the data is sent to the Civic server.

6. **Verify offline.** The Civic SIP server verifies the authenticity and integrity of the attestations received from the user’s mobile app. This process proves that the user data was attested to by Civic and that the user is currently in control of the private keys relevant to the data.

7. **Verify on the blockchain.** The Civic server then verifies that the attestations are still valid on the blockchain and have not been revoked.

8. **Encrypt and cache.** The data is encrypted and cached on the Civic server. Once this data is cached, a polling request from the iframe will receive a response containing an authorization code wrapped in a JWT token. The CivicJS browser-side library passes the token to the parent document. Your site is then responsible for passing the JWT token to your server.

9. **Authorization Code exchange.** Use the Civic SIP client (this library) on your server to communicate with the Civic SIP server and exchange the authorization code (AC) for the requested user data. The SIP server first validates the JWT token, ensuring it was issued by Civic, is being used by the correct application id, and that the expiry time on the token has not lapsed. The enclosed AC is then verified and the encrypted cached data returned.

10. **Decrypt.** Your server receives the encrypted data where it is decrypted using your application secret key. The result will contain a userId and any data requested (such as email, mobile number etc).

11. **Complete user signup.** At this point you can store the necessary data and redirect the user to your app’s logged in experience.

For subsequent logins the `userId` from `UserData` value object can be used to associate the user with your accounts system.

## License
The software licensed under the [MIT license](LICENSE).
