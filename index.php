
<?php

// Reporting All Errors 
error_reporting(E_ALL);
ini_set('display_errors', '1');

// require 'database/migrations/2023083000000_scraping_bulk_kobe.php';
// $connection = new Connection();
// $exec = $connection->getConnection();

require 'vendor/autoload.php';

    use Sunra\PhpSimple\HtmlDomParser;
    use BrowshotAPI\Client;
    use Symfony\Component\DomCrawler\Crawler;

    $apiKey = '99f9a6f251cf4da6ab39fcb004ea08c9';
    $linkToScrape = 'https://www.bestbuy.ca/en-ca/product/plusone-dual-vibrating-massager-pink/16681093';
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
    
// Browshot API key
$browshotApiKey = 'L0ACiKaXAdyDmx7QSzhlEHhz';
//Parsing Using DOMDocument
$dom = new DOMDocument();
@$dom->loadHTML($http_response_body);
$xpath = new DOMXPath($dom);


//================BEST BUY===================================
//Single Image Url BEST BUT
    // XPath query to get the main image URL
    // $mainImageURL = $xpath->evaluate("string(//div[@data-automation='media-gallery-product-image-slider']//img[@class='productImage_1NbKv']/@src)");

    // // XPath query to get additional image URLs
    // $additionalImageURLs = [];
    // $thumbnailItems = $xpath->query("//div[@data-automation='media-gallery-product-image-slider']//div[@class='thumbnailItemContainer_CWxw7']//img[@class='productImage_1NbKv']/@src");
    
    // foreach ($thumbnailItems as $thumbnailItem) {
    //     $additionalImageURLs[] = $thumbnailItem->nodeValue;
    // }
    
    // Output the results
    // echo "<br>Main Image URL: " . $mainImageURL . PHP_EOL;
    // echo "<br>Additional Image URLs: " . implode(", ", $additionalImageURLs) . PHP_EOL;
    
// Extract the product title
// $productTitle = $xpath->query("//h1[@class='productName_2KoPa']")[0]->textContent;

// Extract the brand
// $brandLink = $xpath->query("//div[@class='modelInformation__LaWR']//a[@class='link_3hcyN brand-link']")[0];
// $brandName = trim($brandLink->textContent);

// Extract the model number
//$modelNumber = $xpath->query("//div[@data-automation='MODEL_NUMBER_ID']//span")[0]->textContent;

// Extract the web code
// $webCode = $xpath->query("//div[@data-automation='SKU_ID']//span")[0]->textContent;


// Print the extracted information
// echo "Product Title: " . $productTitle . "\n";
// echo "Brand: " . $brandName . "\n";
// echo "Model Number: " . $modelNumber . "\n";
// echo "Web Code: " . $webCode . "\n";

//Description
// Extract the product description
// $productDescription = $xpath->query('//div[@class="productDescription_2WBlx"]/ul/li');

// if ($productDescription->length > 0) {
//     echo "<br>Product Description:\n";
//     foreach ($productDescription as $descriptionItem) {
//         echo "- " . $descriptionItem->textContent . "<br>";
//     }
// } else {
//     echo "Product description not found.\n";
// }

//Product Specifications
// Find the container with product specifications
// $specContainer = $xpath->query('//div[@data-testid="specifications"]')->item(0);

// if ($specContainer) {
//     $specifications = [];

    // Iterate through the specification groups
    // $groups = $xpath->query('.//div[@class="itemContainer_uqm6b"]', $specContainer);
    // foreach ($groups as $group) {
    //     $groupName = trim($xpath->query('.//div[@class="itemName_GaNqp"]', $group)->item(0)->textContent);
    //     $groupValue = trim($xpath->query('.//div[@class="itemValue_3FLTX"]', $group)->item(0)->textContent);

    //     $specifications[$groupName] = $groupValue;
    // }

    // Now you have the product specifications in the $specifications array
    // You can access individual specifications like this:
    // $productCondition = $specifications['Display'];
    // $depth = $specifications['Dimensions (in)'];
    // $whatsInTheBox = $specifications['Other Input or Output Ports'];
    // $color = $specifications['Colour'];
    // $length = $specifications['Dimensions (in)'];
    // $weight = $specifications['Weight (lbs)'];
    // $batteryPowerSource = $specifications['Battery Type'];
    // $formFactor = $specifications['Backlit Keyboard'];
    // $collection = $specifications['Warranty'];
    // $bodyColor = $specifications['Colour'];
    // $dimensionIn = $specifications['Dimensions (in)'];

    // You can print or use the extracted values as needed
    // echo "Product Condition: $productCondition\n";
    // echo "Depth: $depth\n";
    // echo "What's in the Box: $whatsInTheBox\n";
    // echo "<br>Color: $color\n";
    // echo "<br>Length: $length\n";
    // echo "<br>Weight: $weight\n";
    // echo "Battery/Power Source: $batteryPowerSource\n";
    // echo "Form Factor: $formFactor\n";
    // echo "Collection: $collection\n";
    // echo "Body Color: $bodyColor\n";
    // echo "Dimension (in): $dimensionIn\n";
//}
// XPath query to select all image elements within the slider
//$imageXPathQuery = '//div[@class="thumbnailItemContainer_CWxw7"]/div/div/div[@class="displayingImage_3xp0y"]/img';

// Use XPath to query for image elements
// $imageNodes = $xpath->query($imageXPathQuery);

// Initialize an array to store image URLs
// $imageUrls = [];

// Loop through each image node and extract the 'src' attribute
// foreach ($imageNodes as $imageNode) {
//     $imageUrl = $imageNode->getAttribute('src');
//     $imageUrls[] = $imageUrl;
// }

// Now $imageUrls array contains the URLs of all additional images
// print_r($imageUrls);
//=========================BEST BUY=================================

//==========================WALMART start===========================

//Extracting Product Title
// $titleElement = $xpath->query('//h1[@id="main-title"]');
// if ($titleElement->length > 0) {
//     // Get the text content of the product title element
//     $productTitle = $titleElement->item(0)->textContent;
//     echo "Product Title: " . $productTitle;
// }

//Extracting Product Price

//Extracting the Main Image
//$imageElements = $xpath->query('//div[@data-testid="hero-image-container"]//img[@class="db"]');

// Check if we found the image element
// if ($imageElements->length > 0) {
//     // Access the "src" attribute to get the image URL
//     $nonCleanedImage = $imageElements->item(0)->attributes->getNamedItem('src')->nodeValue;

//     $parsedURL = parse_url($nonCleanedImage);
//     $imageURL = $parsedURL['scheme'] . '://' . $parsedURL['host'] . $parsedURL['path'];
// }
//  echo "Images: ".$imageURL;

//Selecting Other Multiple Alternative Images
// $carouselContainer = $xpath->query('//div[@data-testid="vertical-carousel-container"]');

// if ($carouselContainer->length > 0) {
//     // Within the carousel container, find all the image buttons
//     $imageButtons = $xpath->query('.//button[@data-testid="item-page-vertical-carousel-hero-image-button"]', $carouselContainer->item(0));

//     // Initialize an array to store the image URLs
//     $alternateImageURLs = [];

//     // Loop through each image button and extract the image URL
//     foreach ($imageButtons as $button) {
//         $imageElement = $xpath->query('.//img', $button);

//         if ($imageElement->length > 0) {
//             $nonCleanedImageAlt = $imageElement->item(0)->attributes->getNamedItem('src')->nodeValue;

//             $parsedURL = parse_url($nonCleanedImageAlt);
//             $imageURLAlt = $parsedURL['scheme'] . '://' . $parsedURL['host'] . $parsedURL['path'];
//             $alternateImageURLs[] = $imageURLAlt;
//         }
//     }

//     // Print or use the array of alternate image URLs as needed
//     foreach ($alternateImageURLs as $index => $url) {
//         echo "Alternate Image $index URL: $url<br>";
//     }
// }

//Product Descriptions

// Target the main product details section
// $productDetailsSection = $xpath->query('//section[data-testid="product-description"]');

// if ($productDetailsSection->length > 0) {
//     // Find and extract the product details content
//     $productDetailsContent = $xpath->query('.//div[@data-testid="product-description-content"]/div/span/div[@class="dangerous-html mb3"]', $productDetailsSection->item(0));

//     if ($productDetailsContent->length > 0) {
//         // Loop through and output the product details
//         foreach ($productDetailsContent as $content) {
//             echo "<br>Product Details: " . $content->textContent;
//         }
//     } else {
//         echo "Product details content not found.";
//     }
// } else {
//     echo "Product details section not found.";
// }

//Extract Product Details 
// $sku = '';
// $upc = '';

// //======top div=====

// $topLevelDivs = $xpath->query('//div[@class="w_8XBa w_n9r1 w_JFBv"]');

// if ($topLevelDivs->length > 0) {
//     $flexContainers = $xpath->query('.//div[@data-testid="flex-container" and contains(@class, "flex undefined flex-column h-100")]', $topLevelDivs->item(0));

//     if ($flexContainers->length > 0) {
//         $expandCollapseSection = $xpath->query('.//section', $flexContainers->item(0));

//         if ($expandCollapseSection->length > 0) {
//             echo "Section found!";
//         } else {
//             echo "Section NOT found.";
//         }
//     } else {
//         echo "Flex containers not found.";
//     }
// } else {
//     echo "Top-level divs not found.";
// }

///====end top div===







// Find the container div for the specifications
// $specificationsContainer = $xpath->query('//div[@class="w_zz0G expand-collapse-content"]')->item(0);

// if ($specificationsContainer) {

//     var_dump($specificationsContainer);
//     echo "Found: ". $specificationsContainer;
    // Within the specifications container, find the specific div with class "pb2"
    // $specDivs = $xpath->query('.//div[@class="pb2"]', $specificationsContainer);

    // foreach ($specDivs as $specDiv) {
    //     // Find the label and value within the div
    //     $label = $xpath->query('.//h3[@class="flex items-center mv0 lh-copy f5 pb1 dark-gray"]', $specDiv)->item(0)->textContent;
    //     $value = $xpath->query('.//div[@class="mv0 lh-copy mid-gray f6"]//span', $specDiv)->item(0)->textContent;

    //     // Clean up the label and value
    //     $label = trim($label);
    //     $value = trim($value);

    //     if ($label === 'SKU') {
    //         $sku = $value;
    //     } elseif ($label === 'Universal Product Code (UPC check)') {
    //         $upc = $value;
    //     }
    // }
//}

// Now $sku and $upc contain the extracted values





//==========================WALMART End=============================

// Initialize an array to store the scraped data
// $data = array();

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
// $featureBullets = $dom->getElementById('feature-bullets');
// $descriptions = array(); 

// if ($featureBullets) {
//     $liItems = $featureBullets->getElementsByTagName('li');
//     foreach ($liItems as $liItem) {
//         // Get the first span element
//         $span = $liItem->getElementsByTagName('span')->item(0); 
//         if ($span) {
//             $description = $span->nodeValue;
//             $descriptions[] = $description; 
//         }
//     }
// } else {
//     echo "Description Not Found 1";
// }
// Extract the Description from the 2nd structure
// $expander = $dom->getElementById('productFactsDesktopExpander');

// if ($expander) {
//     $liItems = $expander->getElementsByTagName('li');

//     foreach ($liItems as $liItem) {
//         $span = $liItem->getElementsByTagName('span')->item(0);
//         if ($span) {
//             $description = $span->nodeValue;
//             $descriptions[] = $description;
//         }
//     }
// } else {
//     echo "Description Not Found 2!";
// }



// Now $descriptions contains the descriptions from the specific structure



// var_dump($descriptions);
// // Define the sections you want to scrape
// $sections = array(
//     'Technical Details' => 'productDetails_techSpec_section_1',
//     'Additional Information' => 'productDetails_detailBullets_sections1',
// );

// Define the attributes you want to extract
// $attributes = array(
//     'Manufacturer',
//     'Item model number',
//     'Parcel Dimensions',
//     'ASIN',
//     'Fabric Type',
//     'Place of Business',
//     'Care Instructions',
//     'Batteries Required',
//     'Date First Available',
//     'Item Weight',
//     'Included components',
//     'Maximum Recommended Load',
//     'Size',
// );


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
//BEST BUY
//===============

