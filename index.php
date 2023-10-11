<?php

// Reporting All Errors 
error_reporting(E_ALL);
ini_set('display_errors', '1');

// require 'database/migrations/2023083000000_scraping_bulk_kobe.php';
// $connection = new Connection();
// $exec = $connection->getConnection();

require 'vendor/autoload.php';

//including namespaces
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;
use Sunra\PhpSimple\HtmlDomParser;

// Adding User-Agents Headers, Handling Cookies
$userAgents = ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36'];
$userAgent = $userAgents[array_rand($userAgents)];
$cookieJar =  new CookieJar();

//Create class objects & Adding Headers
$client = new Client([
    'headers' => ['User-Agent' => $userAgent], 
    'cookies' => $cookieJar
]);

//Adding Delays 2 to 5 Seconds
$delays =  rand(2, 5);
sleep($delays);

//---807 Empire----
$endPoint = "https://807empire.ca/product/the-lighthouse-hoodie-regular/";

$response = $client->request('GET', $endPoint);
$html = $response->getBody()->getContents();

$html1 = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <div>
            <a href='https://google.com'>anchor1</a>
            <div class='inner'>
                <a href='https://youtube.com'>anchor2</a>
            </div>
        </div>
    </body>
</html>
HTML;
//Creating crawler object
$crawler = new Crawler($html1);

$crawler->filterXPath('//body//*')
->reduce(function (Crawler $node, $i){
    return $node->nodeName() === 'div';
})
->each(function (Crawler $node, $i){
    if($node->nodeName() ==='div')
    echo "Node: $i \n";
    echo "Tag: ", $node->nodeName(), "\n";
    echo "Outer HTML: ", $node->outerHtml(), "\n";

});

// Add delay 1, 5 Sec
sleep(rand(1,5));
?>