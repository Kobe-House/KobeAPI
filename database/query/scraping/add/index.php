    <?php
    // Reporting All Errors 
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    //Setting Headers for Cross Origin Resource Sharing
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        // Return only the headers and not the content
        //header('Access-Control-Allow-Origin: http://localhost:3000');
        header("Access-Control-Allow-Origin: http://sellerzone.io");
        header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        header('Content-Type: application/json;');
        exit(0);
    }
    // Regular request processing
    //header('Access-Control-Allow-Origin: http://localhost:3000');
    header("Access-Control-Allow-Origin: http://sellerzone.io");
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Content-Type: application/json;');

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

    //Firebase to decode the token and get claims
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    $apiKey = '76c01917efb5461fb2f23e6ab7551885';

    //Get JSON 
    $json = file_get_contents('php://input', true);
    $data = json_decode($json);

    //--------Get Scraping Source-----
    $source = '';
    //-------Getting the URL--------
    $scrapingURL = $data->searchText;

    // ----- Checking the Source ----

    // ---- Define Pattterns for URLS

    $amazonPattern = '/^(https?:\/\/)?(www\.)?(amazon\.com|amazon\.ca)/i';
    $walmartPattern = '/^(https?:\/\/)?(www\.)?(walmart\.com|walmart\.ca)/i';
    $bestbuyPattern = '/^(https?:\/\/)?(www\.)?(bestbuy\.com|bestbuy\.ca)/i';

    // ----- Checking the Preg Match & Assigning Source------
    if (preg_match($amazonPattern, $scrapingURL)) {
        $source = 'amazon';
    } elseif (preg_match($walmartPattern, $scrapingURL)) {
        $source = 'walmart';
    } elseif (preg_match($bestbuyPattern, $scrapingURL)) {
        $source = 'bestbuy';
    } else {
        $source = 'Unkown';
    }

    //-------Get Guid----------------
    //  $guid = $data->guid;

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

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        preg_match('/Bearer\s(\S+)/', $authHeader, $matches);
        if (isset($matches[1])) {
            $token = $matches[1];
            $urufunguzo = "4d46e827c8c29048f1b3de28123ed620fee5f1ab68d5fad46e57c7b3b3e66e45";
            try {
                $decode = JWT::decode($token, new Key($urufunguzo, 'HS512'));
                $guid = $decode->guid;
                if ($guid) {
                    /* -------------------------------------------------------------------------- */
                    /*                                   Amazon                                   */
                    /* -------------------------------------------------------------------------- */

                    if ($source == 'amazon') {
                        require "amazon-add.php";
                    }

                    /* -------------------------------------------------------------------------- */
                    /*                                   Walmart                                  */
                    /* -------------------------------------------------------------------------- */
                    if ($source == 'walmart') {
                        require "walmart-add.php";
                    }

                    /* -------------------------------------------------------------------------- */
                    /*                                   Bestbuy                                  */
                    /* -------------------------------------------------------------------------- */
                    if ($source == 'bestbuy') {
                        require "bestbuy-add.php";
                    }
                } else {
                    echo json_encode("No Guid Found");
                }
            } catch (Exception $e) {
                echo json_encode(["message" => "Error" . $e->getMessage()]);
            }
        } else {
            echo "NOT FOUND";
        }
    } else {
        http_response_code(401);
    }
