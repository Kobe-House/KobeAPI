<?php


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

//INSERT QUERY WALMART
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
if (!$result && !$descriptionResult) {
    echo json_encode(["Product Error:" => $mysqli->error]);
    exit();
} else {
    echo json_encode(["Result:" => "The Insert Query Done!"]);
}
