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
$linkToScrape = 'https://www.amazon.com/Listerine-Anticavity-Mouthwash-Fluoride-Packaging/dp/B00495Q5OW/ref=sr_1_1?_encoding=UTF8&content-id=amzn1.sym.619b2b85-1519-44b0-b10f-193bd022e08c&pd_rd_r=35bc262c-7564-48d6-8f80-c6b8def56bc4&pd_rd_w=ZVnsk&pd_rd_wg=sDC5n&pf_rd_p=619b2b85-1519-44b0-b10f-193bd022e08c&pf_rd_r=WFQCWER3F9CCSPF5M0ZH&qid=1697984151&s=beauty-intl-ship&sr=1-1';

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
//Load the HTML Content
@$dom->loadHTML($http_response_body);
$xpath = new DOMXPath($dom);

// $bodyValues = $xpath->query('//body/*');
// var_dump($bodyValues);
// foreach($bodyValues as $node){
//     echo "Node Name: " . $node->nodeName . "\n";
//     echo "Node Content: " . $node->textContent . "\n\n";
// }
//Extract the Title of the Page
//$divTitleSection = $xpath->query('//body//div[@id="titleSection"]');

//Getting the The product title
// $divTitleSection = $xpath->query('//body//div[@id="titleSection"]');

// if($divTitleSection->length > 0){
//     $titleSection = $divTitleSection->item(0);
//     $titleSectionContent = $titleSection->textContent;
//     echo "The image goes here with its name: ".$titleSectionContent;
// }else{
//     echo "Title Section Not Found!";
// }

//Extract Image URL
$imagTagWrapper = $xpath->query('//div[@class="imgTagWrapper"]');
if($imagTagWrapper->length > 0){
    $imageURL = $xpath->query('.//img/@src', $imagTagWrapper->item(0))->item(0)->nodeValue;
    echo "<strong>THE IMAGE URL IS:</strong> ".$imageURL;
}

//Extracting the product title
$titleSection = $xpath->query('//div[@id="titleSection"]');
if($titleSection->length > 0){
    $productTitle = $xpath->query('.//span[@id="productTitle"]', $titleSection->item(0))->item(0)->textContent;
    echo "<br><br><strong> THE PRODUCT TITLE IS:</strong><br>". $productTitle;
}

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

$featureBullets = $dom->getElementById('feature-bullets');
$liItems = $featureBullets->getElementsByTagName('li');

$descriptions = array(); // Initialize an array to store the descriptions

foreach ($liItems as $liItem) {
    $span = $liItem->getElementsByTagName('span')[0]; // Get the first span element
    if ($span) {
        $description = $span->nodeValue;
        $descriptions[] = $description; // Store the description in the array
    }
}

// To echo all descriptions using a foreach loop
echo "<br><strong>PRODUCT DESCRIPTION:</strong><br>";
foreach ($descriptions as $description) {
    echo "<br>- ".$description . PHP_EOL; // Use PHP_EOL for a newline between descriptions
}

// To access a specific description by index (e.g., the first description)
if (isset($descriptions[0])) {
    echo "First description: " . $descriptions[0];
}

?>