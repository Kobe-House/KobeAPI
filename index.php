
<?php
// Reporting All Errors 
error_reporting(E_ALL);
ini_set('display_errors', '1');

// require 'database/migrations/2023083000000_scraping_bulk_kobe.php';
// $connection = new Connection();
// $exec = $connection->getConnection();

require 'vendor/autoload.php';


$client = new GuzzleHttp\Client();
$scrapingURL = "https://www.amazon.ca/Apple-AirPods-Charging-Latest-Model/dp/B07PXGQC1Q/ref=sr_1_5?keywords=airpods&qid=1700232866&sr=8-5";
$apiKey = "b182690c808e4464a4cb354704ffe391";

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

// Parsing Using DOMDocument
$dom = new DOMDocument();
@$dom->loadHTML($http_response_body);
$xpath = new DOMXPath($dom);

// Use the provided XPath expression to get the image sources
$altImageSources = [];
$nodes = $xpath->query("//span[@class='a-button-text']//img/@src");
foreach ($nodes as $node) {
    $altImageSources[] = $node->value;
}

// Now $imageSources array contains the URLs of the images
print_r($imageSources);

?>


