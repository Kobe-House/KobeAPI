<?php
$apiKey = "76c01917efb5461fb2f23e6ab7551885";

require 'vendor/autoload.php';

$apiKey = '76c01917efb5461fb2f23e6ab7551885';
$scrapingURL = 'https://sinalite.com/en_ca/print-products/calendars/80lb-gloss-text.html';

$client = new GuzzleHttp\Client();

$response = $client->request('POST', 'https://api.zyte.com/v1/extract', [
    'auth' => [$apiKey, ''],
    'headers' => ['Accept-Encoding' => 'gzip'],
    'json' => [
        'url' => $scrapingURL,
        'httpResponseBody' => true
    ],
]);

$dataAPI = json_decode($response->getBody());
$http_response_body = base64_decode($dataAPI->httpResponseBody);

//Parsing Using DOMDocument
$dom = new DOMDocument();
@$dom->loadHTML($http_response_body);
$xpath = new DOMXPath($dom);

$h1Text = $xpath->query('.//h1', $productHeader)->item(0)->nodeValue;
