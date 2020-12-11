# Kuda Open Api For PHP

I needed to work with the kuda open api but it seems that the progress for the work on the openapi-php library is not ready yet.
This library can help fill that gap till they have one ready.

## Installation

```bash
> composer require prosperoking/kudaopenapi
```

## Usage

```php

include './vendor/autoload.php';


use Prosperoking\KudaOpenApi\KudaOpenApi;

// initialize the object and pass the path or string of your key in pem format
$openApi = new KudaOpenApi(
    __DIR__.'/private.pem',
    __DIR__.'/public.pem',
    'nb3mv7L1g0FdIwT9AxBs'
);
try {
    // this will return the account information
    var_dump($openApi->getAccountInfo([
        'beneficiaryAccountNumber'=> '0115745615',
        'beneficiaryBankCode'=>'999058'
    ]));

} catch (\Exception $th) {
    var_dump($th->getMessage());
}

```

> note that if you don't pass a referenceid the libray will generate one for you using php bin2hex(random_bytes(10))

You can also make request using the **makeRequest** method

```php

use Prosperoking\KudaOpenApi\KudaOpenApi;
use Prosperoking\KudaOpenApi\ServiceTypes;
// initialize the object and pass the path or string of your key in pem format
$openApi = new KudaOpenApi(
    __DIR__.'/private.pem',
    __DIR__.'/public.pem',
    'nb3mv7L1g0FdIwT9AxBs'
);
try {
    $payload = [
        'beneficiaryAccountNumber'=> '0115745615',
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

Here are a what I intend to add a simple methods from the class.

- Create a simple api base similar to kuda openapi-node ✅

- Make Simple Classes to create payloads to be used in the makeRequest object to help intelisense support. ⏳

- Create tests ⏳
