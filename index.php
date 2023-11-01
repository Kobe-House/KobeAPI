<?php

// Reporting All Errors 
error_reporting(E_ALL);
ini_set('display_errors', '1');

// require 'database/migrations/2023083000000_scraping_bulk_kobe.php';
// $connection = new Connection();
// $exec = $connection->getConnection();

require 'vendor/autoload.php';

//including namespaces
// use GuzzleHttp\Client;
// use GuzzleHttp\Cookie\CookieJar;
// use Symfony\Component\DomCrawler\Crawler;
// use Sunra\PhpSimple\HtmlDomParser;

// Adding User-Agents Headers, Handling Cookies
// $userAgents = ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36'];
// $userAgent = $userAgents[array_rand($userAgents)];
// $cookieJar =  new CookieJar();

// //Create class objects & Adding Headers
// $client = new Client([
//     'headers' => ['User-Agent' => $userAgent], 
//     'cookies' => $cookieJar
// ]);

// //Adding Delays 2 to 5 Seconds
// $delays =  rand(2, 5);
// sleep($delays);

// //---807 Empire----
// $endPoint = "https://807empire.ca/product/the-lighthouse-hoodie-regular/";

// $response = $client->request('GET', $endPoint);
// $html = $response->getBody()->getContents();

// $html1 = <<<'HTML'
// <!DOCTYPE html>
// <html>
//     <body>
//         <div>
//             <a href='https://google.com'>anchor1</a>
//             <div class='inner'>
//                 <a href='https://youtube.com'>anchor2</a>
//             </div>
//         </div>
//     </body>
// </html>
// HTML;
// //Creating crawler object
// $crawler = new Crawler($html1);

// $crawler->filterXPath('//body//*')
// ->reduce(function (Crawler $node, $i){
//     return $node->nodeName() === 'div';
// })
// ->each(function (Crawler $node, $i){
//     if($node->nodeName() ==='div')
//     echo "Node: $i \n";
//     echo "Tag: ", $node->nodeName(), "\n";
//     echo "Outer HTML: ", $node->outerHtml(), "\n";

// });

// // Add delay 1, 5 Sec
// sleep(rand(1,5));
$apiKey = '99f9a6f251cf4da6ab39fcb004ea08c9';
//$linkToScrape = 'https://www.amazon.com/dp/B0CCK33WLB/ref=s9_acsd_al_bw_c2_x_3_t?pf_rd_m=ATVPDKIKX0DER&pf_rd_s=merchandised-search-11&pf_rd_r=AZJES0BMXEFS8HTC38SQ&pf_rd_t=101&pf_rd_p=98d75f35-c075-4148-beb3-9fa4081bd16a&pf_rd_i=1055398';
$linkToScrape = 'https://www.amazon.ca/dp/B0CG8XJDP5/ref=sspa_dk_detail_4?psc=1&pd_rd_i=B0CG8XJDP5&pd_rd_w=1vs95&content-id=amzn1.sym.d8c43617-c625-45bd-a63f-ad8715c2c055&pf_rd_p=d8c43617-c625-45bd-a63f-ad8715c2c055&pf_rd_r=WC49QT2Z74XGH5VNKTPV&pd_rd_wg=SHHNp&pd_rd_r=762fc926-5eef-4b6e-95fb-e9d968df3cdf&s=apparel&sp_csd=d2lkZ2V0TmFtZT1zcF9kZXRhaWw';

$client = new GuzzleHttp\Client();
$response = $client->request('POST', 'https://api.zyte.com/v1/extract', [
    'auth' => [$apiKey, ''],
    'headers' => ['Accept-Encoding' => 'gzip'],
    'json' => [
        'url' => $linkToScrape,  
        'httpResponseBody' => true 
    ],
]);
$data = json_decode($response->getBody());
$http_response_body = base64_decode($data->httpResponseBody);
//var_dump($http_response_body);
//Parsing Using DOMDocument
$dom = new DOMDocument();
// Load the HTML Content
@$dom->loadHTML($http_response_body);
$xpath = new DOMXPath($dom);

// Initialize an array to store the scraped data
$data = array();

//====ECTACTING THE UL PRODUCT DETAILS=====
// Access individual data items
// $dateFirstAvailable = '';
// $placeOfBusiness = '';
// $asin = '';
// $department = '';

// $elements = $xpath->query('//div[@id="detailBullets_feature_div"]//li//span[@class="a-list-item"]');

// foreach ($elements as $element) {
//     $label = $element->getElementsByTagName('span')->item(0)->textContent;
//     $value = $element->getElementsByTagName('span')->item(1)->textContent;

//     // Clean up the label and value
//     $label = trim($label);
//     $value = trim($value);

//     if (strpos($label, 'Date First Available') !== false) {
//         $dateFirstAvailable = $value;
//     } elseif (strpos($label, 'Place of Business') !== false) {
//         $placeOfBusiness = $value;
//     } elseif (strpos($label, 'ASIN') !== false) {
//         $asin = $value;
//     } elseif (strpos($label, 'Department') !== false) {
//         $department = $value;
//     }
// }

// Extract the Description 1st structure
$featureBullets = $dom->getElementById('feature-bullets');
$descriptions = array(); 

if ($featureBullets) {
    $liItems = $featureBullets->getElementsByTagName('li');
    foreach ($liItems as $liItem) {
        // Get the first span element
        $span = $liItem->getElementsByTagName('span')->item(0); 
        if ($span) {
            $description = $span->nodeValue;
            $descriptions[] = $description; 
        }
    }
} else {
    echo "Description Not Found 1";
}
// Extract the Description from the 2nd structure
$expander = $dom->getElementById('productFactsDesktopExpander');

if ($expander) {
    $liItems = $expander->getElementsByTagName('li');

    foreach ($liItems as $liItem) {
        $span = $liItem->getElementsByTagName('span')->item(0);
        if ($span) {
            $description = $span->nodeValue;
            $descriptions[] = $description;
        }
    }
} else {
    echo "Description Not Found 2!";
}



// Now $descriptions contains the descriptions from the specific structure



var_dump($descriptions);
// Define the sections you want to scrape
$sections = array(
    'Technical Details' => 'productDetails_techSpec_section_1',
    'Additional Information' => 'productDetails_detailBullets_sections1',
);

// Define the attributes you want to extract
$attributes = array(
    'Manufacturer',
    'Item model number',
    'Parcel Dimensions',
    'ASIN',
    'Fabric Type',
    'Place of Business',
    'Care Instructions',
    'Batteries Required',
    'Date First Available',
    'Item Weight',
    'Included components',
    'Maximum Recommended Load',
    'Size',
);


// Loop through the sections and attributes
// foreach ($sections as $sectionName => $sectionId) {
//     // Find the section by ID
//     $section = $xpath->query("//*[@id='$sectionId']");

//     if ($section->length > 0) {
//         // Find the rows within the section
//         $rows = $xpath->query(".//tr", $section->item(0));

//         foreach ($rows as $row) {
//             // Extract the header and data cells
//             $header = trim($xpath->query(".//th", $row)->item(0)->textContent);
//             $dataCell = trim($xpath->query(".//td", $row)->item(0)->textContent);

//             // Check if the header is in the list of attributes you want to scrape
//             if (in_array($header, $attributes)) {
//                 $data[$sectionName][$header] = $dataCell;
//             }
//         }
//     }
// }

//---------------------------WORKIND UL PRODUCT DETAILS----------------------------

// Scenario 2: Check if product details are in a list (ul structure)
// $ulSections = $xpath->query("//div[@id='detailBulletsWrapper_feature_div']//ul[contains(@class, 'a-unordered-list')]");
// foreach ($ulSections as $ul) {
//     $listItems = $xpath->query(".//li", $ul);
//     foreach ($listItems as $listItem) {
//         $dataElements = $xpath->query(".//span[contains(@class, 'a-text-bold')]", $listItem);

//         if ($dataElements->length === 1) {
//             $header = trim($dataElements->item(0)->textContent);
//             $value = trim($listItem->textContent);

//             // Remove the header text from the value
//             $value = str_replace($header, '', $value);

//             $data['Product Details'][$header] = $value;
//         }
//     }
// }
//-----------------try new ul scraping--------------------

// Now, $data should contain the scraped product details
//var_dump($data);
// $technicalDetails = $data['Technical Details'];
// $additionalInformation = $data['Additional Information'];

// Specific attributes you want to echo
// echo "Item Model Number: " . $technicalDetails['Item model number'] . "<br>";
// echo "Parcel Dimensions: " . $technicalDetails['Parcel Dimensions'] . "<br>";
// echo "ASIN: " . $additionalInformation['ASIN'] . "<br>";
// echo "Date First Available: " . $additionalInformation['Date First Available'] . "<br>";
// echo "Manufacturer: " . $additionalInformation['Manufacturer'] . "<br>";
// // And so on for other attributes
// $productDetails = $data['Product Details'];

// Specific attributes you want to echo
// echo "Parcel Dimensions: " . $productDetails['Parcel Dimensions  : '] . "<br>";
// echo "Date First Available: " . $productDetails['Date First Available  : '] . "<br>";
// echo "Manufacturer: " . $productDetails['Manufacturer  : '] . "<br>";
// echo "Place of Business: " . $productDetails['Place of Business  : '] . "<br>";
// echo "ASIN: " . $productDetails['ASIN  : '] . "<br>";
// echo "Item Model Number: " . $productDetails['Item model number  : '] . "<br>";
// And so on for other attributes


//----------------------END PRODUCT UL---------------------------------------------
//Extract The Descriptions
// $featureBullets = $xpath->query('//div[@id="feature-bullets"]');
// $descriptions = [];

// if ($featureBullets->length > 0) {
//     $descriptionList = $xpath->query('.//ul[@class="a-unordered-list"]//li/span[@class="a-list-item"]', $featureBullets->item(0));
//     foreach ($descriptionList as $description) {
//         $descriptions[] = $description->textContent;
//     }
// }
// echo "Descriptions: " . implode("\n", $descriptions);

//----------------------------- WORKIND SCRAPE ------------------------------------------

// Extracting Brand Name
// $brandNameElement = $xpath->query('//tr[contains(@class, "po-brand")]//span[@class="a-size-base po-break-word"]')->item(0);
// $brandName1 = $brandNameElement ? $brandNameElement->textContent : "N/A";

// Extracting Model Name
// $colorElement = $xpath->query('//tr[contains(@class, "po-color")]//span[@class="a-size-base po-break-word"]')->item(0);
// $color1 = $colorElement ? $colorElement->textContent : "N/A";

// Extracting Special Feature
// $specialFeatureElement = $xpath->query('//tr[contains(@class, "po-special_feature")]//span[@class="a-size-base po-break-word"]')->item(0);
// $specialFeature = $specialFeatureElement ? $specialFeatureElement->textContent : "N/A";

// Extracting Age Range
// $ageRangeElement = $xpath->query('//tr[contains(@class, "po-age_range_description")]//span[@class="a-size-base po-break-word"]')->item(0);
// $ageRange = $ageRangeElement ? $ageRangeElement->textContent : "N/A";

// Extracting Width Height
// $dimensionElement = $xpath->query('//tr[contains(@class, "po-item_depth_width_height")]//span[@class="a-size-base po-break-word"]')->item(0);
// $dimensionElement = $dimensionElement ? $dimensionElement->textContent : "N/A";
//-------------------------------------END OF WORKING--------------------------------------------
?>