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

//UL Variables Amazon
$dateFirstAvailableUL = '';
$placeOfBusinessUL = '';
$asinUL = '';
$departmentUL = '';
$manufacturerUL = '';
$itemWeightUL = '';
$itemDimensionUL1 = '';
$itemDimensionUL2 = '';
$itemModelNumberUL1 = '';
$itemModelNumberUL2 = '';
$sizeUL = '';
$colorUL = '';
$brandUL = '';
$specialFeaturesUL = '';

//Variable Declaration For Final Data to Insert into the Database Amazon
$asinFinal = '';
$manufacturerFinal = '';
$brandFinal = '';
$itemWeightFinal = '';
$itemDimensionFinal = '';
$sizeFinal = '';
$widthHeightFinal = '';
$ageRangeFinal = '';
$colorFinal = '';
$specialFeaturesFinal = '';
$itemModelNumberFinal = '';

//Get Scraping Source
$source = $data->source;

/* -------------------------------------------------------------------------- */
/*                                   Amazon                                   */
/* -------------------------------------------------------------------------- */

if ($source == 'amazon') {

    // Scenario 1: Check if product details are in a list (ul structure)
    $elements = $xpath->query('//div[@id="detailBullets_feature_div"]//li//span[@class="a-list-item"]');

    if ($elements) {
        foreach ($elements as $element) {
            $label = $element->getElementsByTagName('span')->item(0)->textContent;
            $value = $element->getElementsByTagName('span')->item(1)->textContent;

            // Clean up the label and value
            $label = trim($label);
            $value = trim($value);

            if (strpos($label, 'Date First Available') !== false) {
                $dateFirstAvailableUL = $value;
            } elseif (strpos($label, 'Place of Business') !== false) {
                $placeOfBusinessUL = $value;
            } elseif (strpos($label, 'ASIN') !== false) {
                $asinUL = $value;
            } elseif (strpos($label, 'Department') !== false) {
                $departmentUL = $value;
            } elseif (strpos($label, 'Manufacturer') !== false) {
                $manufacturerUL = $value;
            } elseif (strpos($label, 'Brand') !== false) {
                $brandUL = $value;
            } elseif (strpos($label, 'Parcel Dimensions') !== false) {
                $itemDimensionUL1 = $value;
            } elseif (strpos($label, 'Product Dimensions') !== false) {
                $itemDimensionUL2 = $value;
            } elseif (strpos($label, 'Model Number') !== false) {
                $itemModelNumberUL1 = $value;
            } elseif (strpos($label, 'Item model number') !== false) {
                $itemModelNumberUL2 = $value;
            } elseif (strpos($label, 'Special Features') !== false) {
                $specialFeaturesUL = $value;
            } elseif (strpos($label, 'Manufacturer') !== false) {
                $sizeUL = $value;
            } elseif (strpos($label, 'Colour') !== false) {
                $colorUL = $value;
            }
        }
    } else {
        echo "Product Details Nothing Found";
    }


    //Extracting the product title
    $titleSection = $xpath->query('//div[@id="titleSection"]');
    if ($titleSection->length > 0) {
        $productTitle = $xpath->query('.//span[@id="productTitle"]', $titleSection->item(0))->item(0)->textContent;
        $productTitle = $mysqli->real_escape_string($productTitle);
    }
    //Extract Image URL
    $imagTagWrapper = $xpath->query('//div[@class="imgTagWrapper"]');
    if ($imagTagWrapper->length > 0) {
        $imageURL = $xpath->query('.//img/@src', $imagTagWrapper->item(0))->item(0)->nodeValue;
    }


    //Extract the Description 1st structure
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
    }

    //Scenario 2: Scrape description in TABLE like structure

    // Initialize an array to store Table description data
    $dataForProductDescription = array();

    // Define the sections you want to scrape
    $sections = array(
        'Technical Details' => 'productDetails_techSpec_section_1',
        'Additional Information' => 'productDetails_detailBullets_sections1',
    );

    // Define the attributes you want to extract
    $attributes = array(
        'Manufacturer',
        'Manufacturer reference',
        'Item model number',
        'Model Number',
        'Parcel Dimensions',
        'Product Dimensions',
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
        'Brand',
        'Special Features',
    );

    // Loop through the sections and attributes
    foreach ($sections as $sectionName => $sectionId) {
        // Find the section by ID
        $section = $xpath->query("//*[@id='$sectionId']");

        if ($section->length > 0) {
            // Find the rows within the section
            $rows = $xpath->query(".//tr", $section->item(0));

            foreach ($rows as $row) {

                // Extract the header and data cells
                $header = trim($xpath->query(".//th", $row)->item(0)->textContent);
                $dataCell = trim($xpath->query(".//td", $row)->item(0)->textContent);

                // Check if the header is in the list of attributes to scrape
                if (in_array($header, $attributes)) {
                    $dataForProductDescription[$sectionName][$header] = $dataCell;
                }
            }
        }
    }

    //Get information from `dataForProductDescription` array and Prep em to be inserted
    $technicalDetails = isset($dataForProductDescription['Technical Details']) ? $dataForProductDescription['Technical Details'] : array();
    $additionalInformation = isset($dataForProductDescription['Additional Information']) ? $dataForProductDescription['Additional Information'] : array();

    // Check and assign values or "N/A"
    $itemModelNumberTABLE1 = isset($technicalDetails['Item model number']) ? $technicalDetails['Item model number'] : (
        isset($additionalInformation['Item model number']) ? $additionalInformation['Item model number'] : ''
    );
    $itemModelNumberTABLE2 = isset($technicalDetails['Model Number']) ? $technicalDetails['Model Number'] : (
        isset($additionalInformation['Model Number']) ? $additionalInformation['Model Number'] : ''
    );
    $specialFeaturesTABLE = isset($technicalDetails['Special Features']) ? $technicalDetails['Special Features'] : (
        isset($additionalInformation['Special Features']) ? $additionalInformation['Special Features'] : ''
    );
    $brandTABLE = isset($technicalDetails['Brand']) ? $technicalDetails['Brand'] : (
        isset($additionalInformation['Brand']) ? $additionalInformation['Brand'] : ''
    );
    $itemDimensionsTABLE1 = isset($technicalDetails['Parcel Dimensions']) ? $technicalDetails['Parcel Dimensions'] : (
        isset($additionalInformation['Parcel Dimensions']) ? $additionalInformation['Parcel Dimensions'] : ''
    );
    $itemDimensionsTABLE2 = isset($technicalDetails['Product Dimensions']) ? $technicalDetails['Product Dimensions'] : (
        isset($additionalInformation['Product Dimensions']) ? $additionalInformation['Product Dimensions'] : ''
    );

    $asinTABLE = isset($technicalDetails['ASIN']) ? $technicalDetails['ASIN'] : (
        isset($additionalInformation['ASIN']) ? $additionalInformation['ASIN'] : ''
    );

    $dateFirstAvailableTABLE = isset($technicalDetails['Date First Available']) ? $technicalDetails['Date First Available'] : (
        isset($additionalInformation['Date First Available']) ? $additionalInformation['Date First Available'] : ''
    );
    $manufacturerTABLE1 = isset($technicalDetails['Manufacturer']) ? $technicalDetails['Manufacturer'] : (
        isset($additionalInformation['Manufacturer']) ? $additionalInformation['Manufacturer'] : ''
    );
    $manufacturerTABLE2 = isset($technicalDetails['Manufacturer reference']) ? $technicalDetails['Manufacturer reference'] : (
        isset($additionalInformation['Manufacturer reference']) ? $additionalInformation['Manufacturer reference'] : ''
    );
    $itemWeightTABLE = isset($technicalDetails['Item Weight']) ? $technicalDetails['Item Weight'] : (
        isset($additionalInformation['Item Weight']) ? $additionalInformation['Item Weight'] :  ''
    );
    $sizeTABLE = isset($technicalDetails['Size']) ? $technicalDetails['Size'] : (
        isset($additionalInformation['Size']) ? $additionalInformation['Size'] :  ''
    );
    $colorTABLE = isset($technicalDetails['Colour']) ? $technicalDetails['Colour'] : (
        isset($additionalInformation['Colour']) ? $additionalInformation['Colour'] :  ''
    );

    // Extracting Brand Name
    $brandNameElement = $xpath->query('//tr[contains(@class, "po-brand")]//span[@class="a-size-base po-break-word"]')->item(0);
    $brandBelowTitle = $brandNameElement ? $brandNameElement->textContent : "N/A";

    // Extracting Model Name
    $colorElement = $xpath->query('//tr[contains(@class, "po-color")]//span[@class="a-size-base po-break-word"]')->item(0);
    $colorBelowTitle = $colorElement ? $colorElement->textContent : "N/A";

    // Extracting Special Feature
    $specialFeatureElement = $xpath->query('//tr[contains(@class, "po-special_feature")]//span[@class="a-size-base po-break-word"]')->item(0);
    $specialFeatureBelowTitle = $specialFeatureElement ? $specialFeatureElement->textContent : "N/A";

    // Extracting Age Range
    $ageRangeElement = $xpath->query('//tr[contains(@class, "po-age_range_description")]//span[@class="a-size-base po-break-word"]')->item(0);
    $ageRangeBelowTitle = $ageRangeElement ? $ageRangeElement->textContent : "N/A";

    // Extracting Width Height
    $dimensionElement = $xpath->query('//tr[contains(@class, "po-item_depth_width_height")]//span[@class="a-size-base po-break-word"]')->item(0);
    $widthHeight = $dimensionElement ? $dimensionElement->textContent : "N/A";

    //Final ASIN
    if (!empty($asinTABLE)) {
        $asinFinal = $asinTABLE;
    } elseif (!empty($asinUL)) {
        $asinFinal = $asinUL;
    }

    //If No ASIN
    if (empty($asinFinal)) {
        $asinFinal = 'N/A';
    }

    //Final Manufacturer
    if (!empty($manufacturerTABLE1)) {
        $manufacturerFinal = $manufacturerTABLE1;
    } elseif (!empty($manufacturerTABLE2)) {
        $manufacturerFinal = $manufacturerTABLE2;
    } elseif (!empty($manufacturerUL)) {
        $manufacturerFinal = $manufacturerUL;
    }

    //If No Manufacturer
    if (empty($manufacturerFinal)) {
        $manufacturerFinal = 'N/A';
    }
    //Final Brand Name
    if (!empty($brandTABLE)) {
        $brandFinal = $brandTABLE;
    } elseif (!empty($brandBelowTitle)) {
        $brandFinal = $brandBelowTitle;
    }

    //If No Brand Name
    if (empty($brandFinal)) {
        $brandFinal = 'N/A';
    }

    //Final Item Weight
    if (!empty($itemWeightTABLE)) {
        $itemWeightFinal = $itemWeightTABLE;
    } elseif (!empty($brandBelowTitle)) {
        $itemWeightFinal = $itemWeightUL;
    }

    //If No Item Weight
    if (empty($itemWeightFinal)) {
        $itemWeightFinal = 'N/A';
    }

    //Final Item Dimension
    if (!empty($itemDimensionsTABLE1)) {
        $itemDimensionFinal = $itemDimensionsTABLE1;
    } elseif (!empty($itemDimensionsTABLE2)) {
        $itemDimensionFinal = $itemDimensionsTABLE2;
    } elseif (!empty($itemDimensionUL1)) {
        $itemDimensionFinal = $itemDimensionUL1;
    } elseif (!empty($itemDimensionUL2)) {
        $itemDimensionFinal = $itemDimensionUL2;
    }

    //If No Item Dimension
    if (empty($itemDimensionFinal)) {
        $itemDimensionFinal = 'N/A';
    }

    //Final Item Model NUmber
    if (!empty($itemModelNumberTABLE1)) {
        $itemModelNumberFinal = $itemModelNumberTABLE1;
    } elseif (!empty($itemModelNumberTABLE2)) {
        $itemModelNumberFinal = $itemModelNumberTABLE2;
    } elseif (!empty($itemModelNumberUL1)) {
        $itemModelNumberFinal = $itemModelNumberUL1;
    } elseif (!empty($itemModelNumberUL2)) {
        $itemModelNumberFinal = $itemModelNumberUL2;
    }

    //If No Item Model NUmber
    if (empty($itemModelNumberFinal)) {
        $itemModelNumberFinal = 'N/A';
    }

    //Final Item Special Features
    if (!empty($specialFeaturesTABLE)) {
        $specialFeaturesFinal = $specialFeaturesTABLE;
    } elseif (!empty($specialFeaturesUL)) {
        $specialFeaturesFinal = $specialFeaturesUL;
    } elseif (!empty($specialFeatureBelowTitle)) {
        $specialFeaturesFinal = $specialFeatureBelowTitle;
    }

    //If No Item Special Features
    if (empty($specialFeaturesFinal)) {
        $specialFeaturesFinal = 'N/A';
    }

    //Final Item Color
    if (!empty($colorTABLE)) {
        $colorFinal = $colorTABLE;
    } elseif (!empty($colorUL)) {
        $colorFinal = $colorUL;
    } elseif (!empty($colorBelowTitle)) {
        $colorFinal = $colorBelowTitle;
    }

    //If No Item Color
    if (empty($colorFinal)) {
        $colorFinal = 'N/A';
    }

    //Final Item Size
    if (!empty($sizeTABLE)) {
        $sizeFinal = $sizeTABLE;
    } elseif (!empty($sizeUL)) {
        $sizeFinal = $sizeUL;
    }

    //If No Item Size
    if (empty($sizeFinal)) {
        $sizeFinal = 'N/A';
    }

    //SANITATION

    $asinFinal = $mysqli->real_escape_string($asinFinal);
    $manufacturerFinal = $mysqli->real_escape_string($manufacturerFinal);
    $brandFinal = $mysqli->real_escape_string($brandFinal);
    $itemWeightFinal = $mysqli->real_escape_string($itemWeightFinal);
    $itemDimensionFinal = $mysqli->real_escape_string($itemDimensionFinal);
    $itemModelNumberFinal = $mysqli->real_escape_string($itemModelNumberFinal);
    $specialFeaturesFinal = $mysqli->real_escape_string($specialFeaturesFinal);
    $sizeFinal = $mysqli->real_escape_string($sizeFinal);
    $colorFinal = $mysqli->real_escape_string($colorFinal);

    //INSERT INTO THE DTABASE
    $sql = "INSERT INTO `product` (`title`, `image_url`, `url`, `created_at`, `item_model`, `parcel_dimensions`, `asin`, `manufacturer`, `item_weight`, `size`, `special_features`, `color`, `brand`, `source`) 
            VALUES ('$productTitle', '$imageURL', '$scrapingURL', now(), '$itemModelNumberFinal', '$itemDimensionFinal', '$asinFinal', '$manufacturerFinal', '$itemWeightFinal', '$sizeFinal', '$specialFeaturesFinal', '$colorFinal', '$brandFinal', '$source')";
    $result = $mysqli->query($sql);

    $productIdAmazon = $mysqli->insert_id;

    //ADD PRODCUT DESCRIPTION
    foreach ($descriptions as $productDescription) {
        $productDescription = trim($mysqli->real_escape_string($productDescription));
        $descriptionInsertSql = "INSERT INTO `product_description` (`product_id`, `description_name`)
                                 VALUES ($productIdAmazon, '$productDescription')";
        $descriptionResult = $mysqli->query($descriptionInsertSql);
    }

    //Additional Images

    //Horizontal Additional Images
    $horAdditionaImages = scrapeAmazon($scrapingURL, $apiKey);

    //Vertical Additional Images
    $vertAdditionaImages = [];
    $nodes = $xpath->query("//span[@class='a-button-text']//img/@src");
    foreach ($nodes as $node) {
        $vertAdditionaImages[] = $node->value;
    }

    if (!empty($horAdditionaImages)) {
        foreach ($horAdditionaImages as $index => $url) {

            $insertAltImagesWalmart = "INSERT INTO `product_images` 
        (`product_id`, `product_image_url`) 
        VALUES('$productIdAmazon', '$url')";

            $altImgResultAmazon = $mysqli->query($insertAltImagesWalmart);
        }
    } else {
        echo json_encode("No Horizantal Images");
    }

    if (!empty($vertAdditionaImages)) {
        foreach ($vertAdditionaImages as $index => $url) {

            $insertAltImagesWalmart = "INSERT INTO `product_images` 
        (`product_id`, `product_image_url`) 
        VALUES('$productIdAmazon', '$url')";

            $altImgResultAmazon = $mysqli->query($insertAltImagesWalmart);
        }
    } else {
        echo json_encode("No Horizantal Images");
    }

    //Error Handling
    if (!$result && !$descriptionResult) {
        echo json_encode(["Product Error:" => $mysqli->error]);
        exit();
    } else {
        echo json_encode(["Result:" => "The Insert Query Done!"]);
    }
} else {
    echo json_encode(["Scraping Source" => "Not Amazon!"]);
}

/* -------------------------------------------------------------------------- */
/*                                   Walmart                                  */
/* -------------------------------------------------------------------------- */

if ($source == 'walmart') {

    //Extracting Product Title
    $titleElement = $xpath->query('//h1[@id="main-title"]');
    if ($titleElement->length > 0) {
        // Get the text content of the product title element
        $productTitleWalmart = $titleElement->item(0)->textContent;
    }
    //Extracting the Main Image
    $imageElements = $xpath->query('//div[@data-testid="hero-image-container"]//img[@class="db"]');

    // Check if we found the image element
    if ($imageElements->length > 0) {
        $nonCleanedImage = $imageElements->item(0)->attributes->getNamedItem('src')->nodeValue;

        $parsedURL = parse_url($nonCleanedImage);
        $imageURLWalmart = $parsedURL['scheme'] . '://' . $parsedURL['host'] . $parsedURL['path'];
    }

    //Calling function to return other product info and decsription

    $walmartProduct = scrapeWalmart($scrapingURL, $apiKey);

    // Extract information from scrappedData
    $size = $walmartProduct['scrappedData']['size'];
    $colour = $walmartProduct['scrappedData']['colour'];
    $sku = $walmartProduct['scrappedData']['sku'];
    $upc = $walmartProduct['scrappedData']['upc'];

    $walmartDescription = $walmartProduct['walmartDescription'][0];

    //Cleaning the description
    $descriptions = [];
    foreach ($walmartDescription as $description) {
        $description = str_replace('<div class="dangerous-html mb3">', '', $description);

        $description = str_replace('</div>', '', $description);

        $descriptions[] = trim($description);
    }

    // Print the individual descriptions
    //print_r($descriptions);

    //INSERT QUERY WALMART
    $sql = "INSERT INTO `product` (`title`, `image_url`, `source`) 
            VALUES ('$productTitleWalmart', '$imageURLWalmart', '$source')";
    $sql = "INSERT INTO 
             `product` (`title`, `image_url`, `created_at`, `item_model`, `asin`, `color`, `source`, `size`, `url`) 
             VALUES ('$productTitleWalmart', '$imageURLWalmart', now(), '$upc', '$sku', '$colour', '$source', '$size', '$scrapingURL')";

    $result = $mysqli->query($sql);
    $productIdWalmart = $mysqli->insert_id;

    //Adding the descriptions

    foreach ($descriptions as $descriptionItem) {

        // Extract text content from the DOMElement
        $descriptionText = trim($descriptionItem);

        // Use real_escape_string on the extracted string
        $descriptionItemEscaped = $mysqli->real_escape_string($descriptionText);

        $descriptionInsertSql = "INSERT INTO `product_description` (`product_id`, `description_name`)
                                    VALUES ($productIdWalmart, '$descriptionItemEscaped')";
        $descriptionResult = $mysqli->query($descriptionInsertSql);
    }

    //Selecting Other Multiple Alternative Images
    $carouselContainer = $xpath->query('//div[@data-testid="vertical-carousel-container"]');

    if ($carouselContainer->length > 0) {
        // Within the carousel container, find all the image buttons
        $imageButtons = $xpath->query('.//button[@data-testid="item-page-vertical-carousel-hero-image-button"]', $carouselContainer->item(0));

        // Initialize an array to store the image URLs
        $alternateImageURLs = [];

        // Loop through each image button and extract the image URL
        foreach ($imageButtons as $button) {
            $imageElement = $xpath->query('.//img', $button);

            if ($imageElement->length > 0) {
                $nonCleanedImageAlt = $imageElement->item(0)->attributes->getNamedItem('src')->nodeValue;

                $parsedURL = parse_url($nonCleanedImageAlt);
                $imageURLAlt = $parsedURL['scheme'] . '://' . $parsedURL['host'] . $parsedURL['path'];
                $alternateImageURLs[] = $imageURLAlt;
            }
        }

        // Use the array of alternate image URLs as needed
        foreach ($alternateImageURLs as $index => $url) {

            $insertAltImagesWalmart = "INSERT INTO `product_images` 
                (`product_id`, `product_image_url`) 
                VALUES('$productIdWalmart', '$url')";

            $resultWalmart = $mysqli->query($insertAltImagesWalmart);
        }
    }
} else {
    echo json_encode(["Scraping Source" => "Not Walmart!"]);
}

/* -------------------------------------------------------------------------- */
/*                                   Bestbuy                                  */
/* -------------------------------------------------------------------------- */

if ($source == 'bestbuy') {

    // Extract the product title
    $productTitleBestBuy = $xpath->query("//h1[@class='productName_2KoPa']")[0]->textContent;
    if (empty($productTitleBestBuy)) {
        $productTitleBestBuy = 'N/A';
    }
    // Extract the brand
    $brandLink = $xpath->query("//div[@class='modelInformation__LaWR']//a[@class='link_3hcyN brand-link']")[0];
    if (empty($brandLink)) {
        $brandLink = 'N/A';
    } else {
        $brandNameBestBuy = trim($brandLink->textContent);
    }

    // Extract the model number
    $modelNumberBestBuy = $xpath->query("//div[@data-automation='MODEL_NUMBER_ID']//span")[0]->textContent;
    if (!empty($modelNumberBestBuy)) {
        $modelNumberBestBuy = 'N/A';
    }

    // Extract the web code
    $webCodeBestBuy = $xpath->query("//div[@data-automation='SKU_ID']//span")[0]->textContent;
    if (!empty($webCwebCodeBestBuyode)) {
        $webCodeBestBuy = 'N/A';
    }

    //Other Product Specification
    // Find the container with product specifications
    $specContainer = $xpath->query('//div[@data-testid="specifications"]')->item(0);

    if ($specContainer) {
        $specifications = [];

        // Iterate through the specification groups
        $groups = $xpath->query('.//div[@class="itemContainer_uqm6b"]', $specContainer);
        foreach ($groups as $group) {
            $groupName = trim($xpath->query('.//div[@class="itemName_GaNqp"]', $group)->item(0)->textContent);
            $groupValue = trim($xpath->query('.//div[@class="itemValue_3FLTX"]', $group)->item(0)->textContent);

            $specifications[$groupName] = $groupValue;
        }

        if (isset($specifications['Colour'])) {
            $colorBestBuy = $specifications['Colour'];
        } else {
            $colorBestBuy = 'N/A';
        }
        if (isset($specifications['Product Condition'])) {
            $productCondition = $specifications['Product Condition'];
        } else {
            $productCondition = 'N/A';
        }
        if (isset($specifications['Weight'])) {
            $weightBestBuy0 = $specifications['Weight'];
        } else {
            $weightBestBuy0 = 'N/A';
        }
        if (isset($specifications['Weight (lbs)'])) {
            $weightBestBuy2 = $specifications['Weight (lbs)'];
        } else {
            $weightBestBuy2 = 'N/A';
        }
        if (isset($specifications['Weight (in)'])) {
            $weightBestBuy3 = $specifications['Weight (in)'];
        } else {
            $weightBestBuy3 = 'N/A';
        }
        if (isset($specifications['Weight (Inches)'])) {
            $weightBestBuy1 = $specifications['Weight (Inches)'];
        } else {
            $weightBestBuy1 = 'N/A';
        }
        if (isset($specifications['Height (lbs)'])) {
            $heightBestBuy0 = $specifications['Height (lbs)'];
        } else {
            $heightBestBuy0 = 'N/A';
        }
        if (isset($specifications['Height (in)'])) {
            $heightBestBuy1 = $specifications['Height (in)'];
        } else {
            $heightBestBuy1 = 'N/A';
        }
        if (isset($specifications['Height (Inches)'])) {
            $heightBestBuy2 = $specifications['Height (Inches)'];
        } else {
            $heightBestBuy2 = 'N/A';
        }
        if (isset($specifications['Height'])) {
            $heightBestBuy3 = $specifications['Height'];
        } else {
            $heightBestBuy3 = 'N/A';
        }
        if (isset($specifications['Dimensions'])) {
            $dimensioBestBuy0 = $specifications['Dimensions'];
        } else {
            $dimensioBestBuy0 = 'N/A';
        }
        if (isset($specifications['Dimensions (in)'])) {
            $dimensioBestBuy1 = $specifications['Dimensions (in)'];
        } else {
            $dimensioBestBuy1 = 'N/A';
        }
        if (isset($specifications['Dimensions (Inches)'])) {
            $dimensioBestBuy2 = $specifications['Dimensions (Inches)'];
        } else {
            $dimensioBestBuy2 = 'N/A';
        }
        if (isset($specifications['Dimensions (lbs)'])) {
            $dimensioBestBuy3 = $specifications['Dimensions (lbs)'];
        } else {
            $dimensioBestBuy3 = 'N/A';
        }

        // $whatsInTheBox = $specifications['Other Input or Output Ports'];
        // $batteryPowerSource = $specifications['Battery Type'];
    }


    $weightBestBuyFinal = "";
    if (!empty($weightBestBuy0)) {
        $weightBestBuyFinal = $weightBestBuy0;
    } elseif (!empty($weightBestBuy1)) {
        $weightBestBuyFinal = $weightBestBuy1;
    } elseif (!empty($weightBestBuy2)) {
        $weightBestBuyFinal = $weightBestBuy2;
    } elseif (!empty($weightBestBuy3)) {
        $weightBestBuyFinal = $weightBestBuy3;
    }

    $heightBestBuyFinal = "";
    if (!empty($heightBestBuy0)) {
        $heightBestBuyFinal = $heightBestBuy0;
    } elseif (!empty($heightBestBuy1)) {
        $heightBestBuyFinal = $heightBestBuy1;
    } elseif (!empty($heightBestBuy2)) {
        $heightBestBuyFinal = $heightBestBuy2;
    } elseif (!empty($heightBestBuy3)) {
        $heightBestBuyFinal = $heightBestBuy3;
    }
    $dimensioBestBuyFinal = "";
    if (!empty($dimensioBestBuy0)) {
        $dimensioBestBuyFinal = $dimensioBestBuy0;
    } elseif (!empty($dimensioBestBuy1)) {
        $dimensioBestBuyFinal = $dimensioBestBuy1;
    } elseif (!empty($dimensioBestBuy2)) {
        $dimensioBestBuyFinal = $dimensioBestBuy2;
    } elseif (!empty($dimensioBestBuy3)) {
        $dimensioBestBuyFinal = $dimensioBestBuy3;
    }

    // Get the main image URL
    $mainImageURL = $xpath->evaluate("string(//div[@data-automation='media-gallery-product-image-slider']//img[@class='productImage_1NbKv']/@src)");

    //INSERT INTO THE DTABASE BEST BUY
    $sql = "INSERT INTO 
        `product` (`title`, `image_url`, `created_at`, `item_model`, `parcel_dimensions`, `asin`, `item_weight`, `color`, `brand`, `source`, `item_height`, `url`) 
        VALUES ('$productTitleBestBuy', '$mainImageURL', now(), '$modelNumberBestBuy', '$dimensioBestBuyFinal', '$webCodeBestBuy', '$weightBestBuyFinal', '$colorBestBuy', '$brandNameBestBuy', '$source', '$heightBestBuyFinal', '$scrapingURL')";
    // $sql = "INSERT INTO 
    // `product` (`title`, `image_url`, `url`, `created_at`, `item_model`, `parcel_dimensions`, `asin`, `manufacturer`, `item_weight`, `size`, `special_features`, `color`, `brand`, `source`) 
    // VALUES ('$productTitleBestBuy', '$imageURL', '$scrapingURL', now(), '$modelNumberBestBuy', '$dimensioBestBuy', '$webCodeBestBuy', '$manufacturerFinal', '$weightBestBuy', '$sizeFinal', '$specialFeaturesFinal', '$colorBestBuy', '$brandNameBestBuy', '$source')";
    $result = $mysqli->query($sql);

    $productIdBestBuy = $mysqli->insert_id;

    // Extract the product description
    $productDescription = $xpath->query('//div[@class="productDescription_2WBlx"]/ul/li');

    if ($productDescription->length > 0) {
        foreach ($productDescription as $descriptionItem) {

            // Extract text content from the DOMElement
            $descriptionText = trim($descriptionItem->nodeValue);

            // Use real_escape_string on the extracted string
            $descriptionItemEscaped = $mysqli->real_escape_string($descriptionText);

            $descriptionInsertSql = "INSERT INTO `product_description` (`product_id`, `description_name`)
                                        VALUES ($productIdBestBuy, '$descriptionItemEscaped')";
            $descriptionResult = $mysqli->query($descriptionInsertSql);
        }
    } else {
        echo "Product description not found.\n";
    }

    // Adding Additional Images
    $bestbuyAdditionalImages = scrapeBestbuy($scrapingURL, $apiKey);

    if (!empty($bestbuyAdditionalImages)) {
        foreach ($bestbuyAdditionalImages as $index => $url) {

            $insertAltImagesBestBuy = "INSERT INTO `product_images` 
            (`product_id`, `product_image_url`) 
            VALUES('$productIdBestBuy', '$url')";

            $altImgResultBestBuy = $mysqli->query($insertAltImagesBestBuy);
        }
    } else {
        echo json_encode("No Additional Images");
    }
} else {
    echo json_encode(["Scraping Source" => "Not BestBuy!"]);
}
