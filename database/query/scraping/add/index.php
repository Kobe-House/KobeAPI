<?php
// Reporting All Errors 
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Setting Headers for Cross Origin Resource Sharing
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept");

//Database Connection
include('../../../migrations/2023083000000_scraping_bulk_kobe.php');
$connection = new Connection();
$mysqli = $connection->getConnection();


//Get JSON 
$json = file_get_contents('php://input', true);
$data = json_decode($json);

//Gettinng Composer autoload
require '../../../../vendor/autoload.php';


// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
// $dotenv->load();
// $api = getenv('API_KEY');

 $apiKey = '99f9a6f251cf4da6ab39fcb004ea08c9';

//SCRAPING PROCESS
if($data->searchText){

   
    
    //Getting the URL
    $scrapingURL = $data->searchText;

    //Implementing Guzzle
    $client = new GuzzleHttp\Client();

    $response = $client->request('POST', 'https://api.zyte.com/v1/extract', [
    'auth' => [$apiKey, ''],
    'headers' => ['Accept-Encoding' => 'gzip'],
    'json' => [
        'url' => $scrapingURL,  
        'httpResponseBody' => true 
        ],
    ]);

    $data = json_decode($response->getBody());
    $http_response_body = base64_decode($data->httpResponseBody);

    //Parsing Using DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($http_response_body);
    $xpath = new DOMXPath($dom);

    //Extracting the product title
    $titleSection = $xpath->query('//div[@id="titleSection"]');
    if($titleSection->length > 0){
        $productTitle = $xpath->query('.//span[@id="productTitle"]', $titleSection->item(0))->item(0)->textContent;
        $productTitle = $mysqli->real_escape_string($productTitle);
    }
    //Extract Image URL
    $imagTagWrapper = $xpath->query('//div[@class="imgTagWrapper"]');
    if($imagTagWrapper->length > 0){
        $imageURL = $xpath->query('.//img/@src', $imagTagWrapper->item(0))->item(0)->nodeValue;
    }

    //Extract the Description
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

    //INSERT INTO THE DTABASE
    $sql = "INSERT INTO `product` (`title`, `image_url`, `url`, `created_at`) 
            VALUES ('$productTitle', '$imageURL', '$scrapingURL', now())";
    $result = $mysqli->query($sql);

    $productId = $mysqli->insert_id;

    //ADD PRODCUT DESCRIPTION
    foreach($descriptions as $productDescription){
        $productDescription = $mysqli->real_escape_string($productDescription);
        $descriptionInsertSql = "INSERT INTO `product_description` (`product_id`, `description_name`)
                                 VALUES ($productId, '$productDescription')";
        $descriptionResult = $mysqli->query($descriptionInsertSql);
    }

    //Error Handling
    if(!$result && !$descriptionResult){
        echo json_encode(["Product Error:" => $mysqli->error]);
        exit();
    }else{
        echo json_encode(["Result:" => "The Insert Query Done!"]);
    }
}
?>