# Kuda Open Api For PHP

I needed to work with the kuda openapi but it seems that the progress for the work on the openapi-php library is not ready yet.
This library can help fill that gap till they have one ready.

## Installation

```bash
> composer require prosperoking/kudaopenapi
```

## Usage

Usage is takes into account the kudaOpenApi v1 and v2. The v1 makes use of private and public key pairs
while the v2 uses an apiKey and email combination without the need to encrypt the body. Below are ways to utilize 
any of the api

### For api v2
> You can get your api key/keys from the kuda developer portal  [here](https://developer.kudabank.com/)

Test and live environment urls in use are
- Test: https://kuda-openapi-uat.kudabank.com/v2
- Live: https://kuda-openapi.kuda.com/v2

```php

include './vendor/autoload.php';


use Prosperoking\KudaOpenApi\KudaOpenApiV2;

$email = "youremail@here.com"
// You can generate this on the developer dashboard
$apiKey = 'xxxxxxxxxxx';

// initialize the object and pass the path or string of your key in pem format
$openApi = KudaOpenApiV2::initLive($email, $apiKey);
// And for test environment 
$openApi = KudaOpenApiV2::initLive($email, $apiKey);

// You can also Use the constructor
$openApi = new KudaOpenApiV2($email,$openApi, null, $baseUrl )
    
try {
    // this will return the account NIBBS information
    var_dump($openApi->getAccountInfo([
        'beneficiaryAccountNumber'=> '1115744617',
        'beneficiaryBankCode'=>'999058',
    ]));

} catch (\Exception $th) {
    var_dump($th->getMessage());
}
```

v2 comes with a cache driver to avoid making unneeded calls for authentication within every 5 hrs this
will help improve the speed of your requests.
You can also write your own cache driver and supply for the client to use and example with laravel is
```php
use Prosperoking\KudaOpenApi\Contracts\IAuthCacheDriver
class MyAwesomeCacheDriver implements IAuthCacheDriver {
    public function setAuthToken(string $value): bool
    {
        // You may decide to encrypt  the value before saving it
        return Cache::set('kuda_key', $value, now()->addMinutes(12);
    }
    public function getAuthToken(): ?string
    {
        // And Decrypt it here before returning it 
        return Cache::get('kuda_key');
    }
}
```

And pass it to the client with any of the methods

```php
include './vendor/autoload.php';


use Prosperoking\KudaOpenApi\KudaOpenApiV2;

$email = "youremail@here.com"
// You can generate this on the developer dashboard
$apiKey = 'xxxxxxxxxxx';

// init your cache driver 
$myCacheDriver = new MyAwesomeCacheDriver()

// initialize the object and pass the path or string of your key in pem format
$openApi = KudaOpenApiV2::initLive($email, $apiKey, $myCacheDriver);
// And for test environment 
$openApi = KudaOpenApiV2::initLive($email, $apiKey, $myCacheDriver);

// You can set the cache driver using the set cache driver method

$openApi->setCacheDriver($myCacheDriver);

// You can also Use the constructor
$openApi = new KudaOpenApiV2($email,$openApi, $myCacheDriver, $baseUrl )
```

### For api v1
```php

include './vendor/autoload.php';


use Prosperoking\KudaOpenApi\KudaOpenApi;

$client = "your_client_id_here"
$baseUrl = 'https://sandbox.kudabank.com/v1';

// initialize the object and pass the path or string of your key in pem format
$openApi = new KudaOpenApi(
    __DIR__.'/private.pem',
    __DIR__.'/public.pem',
    
try {
    // this will return the account NIBSS information
    var_dump($openApi->getAccountInfo([
        'beneficiaryAccountNumber'=> '1115744617',
        'beneficiaryBankCode'=>'999058',
    ]));
    
    // or You can make use of makeRequest method
    $payload = [
        'beneficiaryAccountNumber'=> '1115744615',
        'beneficiaryBankCode'=>'999058'
    ];
    $requestRef= "myunique_identifier"
    // this will return the account information
    var_dump($openApi->makeRequest(
        ServiceTypes::NAME_ENQUIRY,
        $payload,
        $requestRef
    ));

} catch (\Exception $th) {
    var_dump($th->getMessage());
}

```

> note that if you don't pass a referenceid the libray will generate one for you using php bin2hex(random_bytes(10))

You can also make request using the **makeRequest** method

```php

use Prosperoking\KudaOpenApi\KudaOpenApi;
use Prosperoking\KudaOpenApi\ServiceTypes;

$client = "your client id here"
$baseUrl = 'https://sandbox.kudabank.com/v1';
// initialize the object and pass the path or string of your key in pem format
$openApi = new KudaOpenApi(
    __DIR__.'/private.pem',
    __DIR__.'/public.pem',
    $clientId,
    $baseUrl
);
try {
    $payload = [
        'beneficiaryAccountNumber'=> '1115744615',
        'beneficiaryBankCode'=>'999058'
    ];
    $requestRef= "myunique_identifier"
    // this will return the account information
    var_dump($openApi->makeRequest(
        ServiceTypes::NAME_ENQUIRY,
        $payload,
        $requestRef
    ));

} catch (\Exception $th) {
    var_dump($th->getMessage());
}

```

## Road Map

Here are what I intend to add:

- Create a simple api base similar to kuda openapi-node ☑

- Make Simple Classes to create payloads to be used in the makeRequest object to help intelisense support. ⏳

- Add support for laravel ⏳

- Create tests ⏳
