Webgriffe QuiPago (Key Client/CartaSì) PHP library
==========================================

[![Build Status](https://travis-ci.org/webgriffe/lib-quipago.svg?branch=master)](https://travis-ci.org/webgriffe/lib-quipago)

A PHP library for Nexi/QuiPago (Key Client/CartaSì) payment gateway.

Usage
-----

You can generate a payment initialization URL using the `UrlGenerator`:

	$urlGenerator = new Webgriffe\LibQuiPago\PaymentInit\UrlGenerator();
	$url = $urlGenerator->generate(
		'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet',
        'merchant_alias',
        'secret_key',
        'sha1',
        50.50,
        'EUR',
        '1200123',
        'http-cancel-url', // The URL where the user is redirected on failed payment
        'customer@mail.com',
        'http-succes-url', // The URL where the user is redirected on successful payment
        'SESSID123',
        'ITA',
        'http-post-url' // The URL for the server-to-server notification
	);
	
	// Returned $url is https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url_back=http-cancel-url&mail=customer%40mail.com&url=http-succes-url&session_id=SESSID123&languageId=ITA&urlpost=http-post-url&mac=0fa0ca05a13c6b5d0bd1466461319658f7f990bf
	
You can also handle a server-to-server notification by QuiPago using the notification `Handler`:

	// These request params array comes from the QuiPago's HTTP notification request
	$requestParams = array(
        'codTrans' => '1200123',
        'esito' => 'OK',
        'importo' => '5050',
        'divisa' => 'EUR',
        'data' => '20160221',
        'orario' => '181854',
        'codAut' => '123abc',
        'mac' => 'c83cee2a5422189cab2b54ef685b29dc428741dc',
        'alias' => 'merchant_123',
        'session_id' => '123123',
        '$BRAND' => 'Visa',
        'nome' => 'John',
        'cognome' => 'Doe',
        'mail' => 'jd@mail.com',
    );
    
    $handler = new Webgriffe\LibQuiPago\Notification\Handler();
    $handler->handle('secret_key', 'sha1', $requestParams)
    
    // You'll get all parameters mapped    
    $handler->getTransactionCode(); // '1200123'    
    $handler->getAmount(); // 50.50
    $handler->getCurrency(); //'EUR'
    $handler->getTransactionDate(); // \DateTime('21/02/2016 18:18:54')
    $handler->getAuthCode(); // '123abc'
    $handler->getMacFromRequest(); // 'c83cee2a5422189cab2b54ef685b29dc428741dc'
    $handler->getMerchantAlias(); // 'merchant_123'
    $handler->getSessionId(); // '123123'
    $handler->getCardBrand(); // 'Visa'
    $handler->getFirstName(); // 'John'
    $handler->getLastName(); // 'Doe'
    $handler->getEmail(); // 'jd@mail.com'
    
    // And you know if the transaction has been authorized or not by calling
    $handler->isTransactionResultPositive(); // true

You can inject a `Psr\Log\LoggerInterface` logger to both `Webgriffe\LibQuiPago\PaymentInit\UrlGenerator` and `Webgriffe\LibQuiPago\Notification\Handler` to enable logging of internal operations.

ECREQ/ECRES API
---------------

You can also send *ECREQ* messages through API `Client` and receive *ECRES* response messages. Capture and void methods are supported:

	require 'vendor/autoload.php';

    $client = new Webgriffe\LibQuiPago\Api\Client(
        new GuzzleHttp\Client(),
        'payment_3444153',
        'TLGHTOWIZXQPTIZRALWKG',
        'Manu'
    );
    
    /** @var Webgriffe\LibQuiPago\Api\EcResponse $response */
    $response = $client->capture(
        '000000123', // Transaction Code
        Webgriffe\LibQuiPago\Api\EcRequest::REQUEST_TYPE_FIRST_ATTEMPT,
        '000000123', // Operation Id
        105, // Original Amount
        '978', // Currency ISO code
        'TESTOK', // Auth Code
        105, // Operation Amount
        true // Is test request?
    );
    
    /** @var Webgriffe\LibQuiPago\Api\EcResponse $response */
    $response = $client->void(
        '000000123', // Transaction Code
        Webgriffe\LibQuiPago\Api\EcRequest::REQUEST_TYPE_FIRST_ATTEMPT,
        '000000123', // Operation Id
        105, // Original Amount
        '978', // Currency ISO code
        'TESTOK', // Auth Code
        105, // Operation Amount
        true // Is test request?
    );

See the `Webgriffe\LibQuiPago\Api\EcResponse` class to know which data you can retrive from that object.

Contributing
------------

* clone this repo
* composer install
* do your changes
* vendor/bin/phpspec run
* vendor/bin/phpcs --standard=PSR2 src/
* submit a pull request

License
-------

This library is under the MIT license. See the complete license in the LICENSE file.

Credits
-------

Developed by [Webgriffe®](http://www.webgriffe.com/).
