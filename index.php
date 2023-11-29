<?php

include "database/query/dynamics/amazon-script.php";
include "database/query/dynamics/bestbuy-script.php";
include "database/query/dynamics/walmart-script.php";

$apiKey = "76c01917efb5461fb2f23e6ab7551885";

// $linkToScrape = "https://www.bestbuy.ca/en-ca/product/samsung-36-27-cu-ft-french-door-refrigerator-w-water-ice-dispenser-rf27t5201sr-aa-stainless-steel/14481456";

// // $linkToScrape = "https://www.bestbuy.ca/en-ca/product/bebelelo-set-of-6-baby-nursery-bundle-92-graco-modes-jogger-2-0-travel-system-stroller-with-car-seat-crib-bed/16000510";

// $linkToScrape = "https://www.bestbuy.ca/en-ca/product/microsoft-surface-laptop-go-3-12-45-touchscreen-laptop-sandstone-intel-i5-1235u-256gb-ssd-8gb-ram-exclusive-retail-partner/17212075";

// $bestbuyProduct = scrapeBestbuy($linkToScrape, $apiKey);

// echo '<pre>';
// print_r($bestbuyProduct);
// echo '</pre>';
// die();

/* -------------------------------------------------------------------------- */
/*                                   Walmart                                  */
/* -------------------------------------------------------------------------- */

// $linkToScrape = "https://www.walmart.ca/en/ip/george-mens-christmas-shirt-red/6000206539294";

//$linkToScrape = "https://www.walmart.ca/en/ip/hyperx-cloud-ii-gaming-headset-for-pc-ps5-ps4-includes-71-virtual-surround-sound-and-usb-audio-control-box-black-red/7HAXHLZRDTDY";

// $linkToScrape = "https://www.walmart.ca/en/ip/asus-laptop-l410-14-fhd-display-intel-celeron-n4020-processor-ultra-thin-laptop-star-black-l410ma-wb01-cb-4gb-ram-64gb-storage-intel-hd-star-black/6000205412421";

// $walmartProduct= scrapeWalmart($linkToScrape, $apiKey);

// echo '<pre>';
// print_r($walmartProduct);
// echo '</pre>';
// die();




/* -------------------------------------------------------------------------- */
/*                                   Amazon                                   */
/* -------------------------------------------------------------------------- */


//$linkToScrape = "https://www.amazon.ca/Instant-Electric-Pressure-Sterilizer-Stainless/dp/B00FLYWNYQ/?_encoding=UTF8&pd_rd_w=ne5hj&content-id=amzn1.sym.0a4889f1-d999-4e04-a718-34da7dae1e8b&pf_rd_p=0a4889f1-d999-4e04-a718-34da7dae1e8b&pf_rd_r=HWSDVY5XW0WSN93RVE0V&pd_rd_wg=Ad30A&pd_rd_r=3ad9e0ab-873c-40cf-8eb3-bf753088894d&ref_=pd_gw_crs_zg_bs_2206275011&th=1";


// $linkToScrape = "https://www.amazon.ca/ecozy-Portable-Countertop-Self-Cleaning-Standing/dp/B0B498C643/?_encoding=UTF8&pd_rd_w=CB3Lo&content-id=amzn1.sym.50311115-df4f-4b47-980a-43a6074ea041%3Aamzn1.symc.8b620bc3-61d8-46b3-abd9-110539785634&pf_rd_p=50311115-df4f-4b47-980a-43a6074ea041&pf_rd_r=MG6Z8CDNBPT5BRA6TG47&pd_rd_wg=3AM4w&pd_rd_r=9835f9d2-b27d-43a8-a0f1-e7f0e0c2108a&ref_=pd_gw_ci_mcx_mr_hp_d";

// $amazonProduct= scrapeAmazon($linkToScrape, $apiKey);

// echo '<pre>';
// print_r($amazonProduct);
// echo '</pre>';
// die();
require 'vendor/autoload.php';

$apiKey = '76c01917efb5461fb2f23e6ab7551885';
$scrapingURL = 'https://www.amazon.com/Oculus-Virtual-Reality-Headset-Cable-Gaming/dp/B081SHD773/ref=rvi_sccl_3/133-1349499-0948946?pd_rd_w=QdMVo&content-id=amzn1.sym.f5690a4d-f2bb-45d9-9d1b-736fee412437&pf_rd_p=f5690a4d-f2bb-45d9-9d1b-736fee412437&pf_rd_r=JZDTYEAXSTFGVNXTF3VV&pd_rd_wg=3oqyE&pd_rd_r=9810cbde-caac-44c5-8f38-005367ad34a6&pd_rd_i=B081SHD773&psc=1';

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


// Define the attributes you want to scrape
$attributes = array(
    'Manufacturer',
    'Item model number',
    'Product Dimensions',
    'ASIN',
    'Item Weight',
);

// XPath query for the product details table
$productDetailsXPath = "//div[@id='prodDetails']//table[contains(@class, 'prodDetTable')]//tr";

// Find the product details table
$productDetails = $xpath->query($productDetailsXPath);

// Initialize an array to store scraped data
$dataForProductDescription = array();

// Loop through the rows in the product details table
foreach ($productDetails as $row) {
    // Extract the header and data cells
    $header = trim($xpath->query(".//th[contains(@class, 'prodDetSectionEntry')]", $row)->item(0)->textContent);
    $dataCell = trim($xpath->query(".//td[contains(@class, 'prodDetAttrValue')]", $row)->item(0)->textContent);

    // Check if the header is in the list of attributes to scrape
    if (in_array($header, $attributes)) {
        $dataForProductDescription[$header] = $dataCell;
    }
}

// Now $dataForProductDescription should contain the scraped data for the specified attributes
var_dump($dataForProductDescription);

$manufacturer = $dataForProductDescription['Manufacturer'] ?? '';
$itemModelNumber = $dataForProductDescription['Item model number'] ?? '';
$productDimensions = $dataForProductDescription['Product Dimensions'] ?? '';
$asin = $dataForProductDescription['ASIN'] ?? '';
$itemWeight = $dataForProductDescription['Item Weight'] ?? '';

// Now you can use these variables as needed
echo "Manufacturer: $manufacturer\n";
echo "Item Model Number: $itemModelNumber\n";
echo "Product Dimensions: $productDimensions\n";
echo "ASIN: $asin\n";
echo "Item Weight: $itemWeight\n";
