    <?php
    // Reporting All Errors 
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    //Setting Headers for Cross Origin Resource Sharing
    //header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Origin: http://sellerzone.io");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, Content-Type, Accept");

    //Database Connection
    include('../../../migrations/2023083000000_scraping_bulk_kobe.php');
    $connection = new Connection();
    $mysqli = $connection->getConnection();

    // $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
    // $dotenv->load();
    // $api = getenv('API_KEY');

    //===Dynamics loading contents script====
    require '../../../../vendor/autoload.php';
    include '../../dynamics/amazon-script.php';
    include '../../dynamics/bestbuy-script.php';
    include '../../dynamics/walmart-script.php';

    $apiKey = '76c01917efb5461fb2f23e6ab7551885';

    //Get JSON 
    $json = file_get_contents('php://input', true);
    $data = json_decode($json);

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

    $dataAPI = json_decode($response->getBody());
    $http_response_body = base64_decode($dataAPI->httpResponseBody);

    //Parsing Using DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($http_response_body);
    $xpath = new DOMXPath($dom);



    //Get Scraping Source
    $source = $data->source;

    /* -------------------------------------------------------------------------- */
    /*                                   Amazon                                   */
    /* -------------------------------------------------------------------------- */

    // if ($source == 'amazon') {
    //     require "amazon-add.php";
    // }

    /* -------------------------------------------------------------------------- */
    /*                                   Walmart                                  */
    /* -------------------------------------------------------------------------- */
    if ($source == 'walmart') {
        require "walmart-add.php";
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Bestbuy                                  */
    /* -------------------------------------------------------------------------- */
