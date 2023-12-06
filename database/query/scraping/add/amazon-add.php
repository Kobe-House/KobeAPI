<?php

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

echo "<br>asinFinal: ";
var_dump($asinFinal);
echo "<br>manufacturerFinal: ";
var_dump($manufacturerFinal);
echo "<br>brandFinal: ";
var_dump($brandFinal);
echo "<br>itemWeightFinal: ";
var_dump($itemWeightFinal);
echo "<br>itemDimensionFinal: ";
var_dump($itemDimensionFinal);
echo "<br>itemModelNumberFinal: ";
var_dump($itemModelNumberFinal);
echo "<br>specialFeaturesFinal: ";
var_dump($specialFeaturesFinal);
echo "<br>specialFeaturesFinal: ";
var_dump($specialFeaturesFinal);

//INSERT INTO THE DTABASE
$sql = "INSERT INTO `product` (`title`, `image_url`, `url`, `created_at`, `item_model`, `parcel_dimensions`, `asin`, `manufacturer`, `item_weight`, `size`, `special_features`, `color`, `brand`, `source`) 
            VALUES ('$productTitle', '$imageURL', '$scrapingURL', now(), '$itemModelNumberFinal', '$itemDimensionFinal', '$asinFinal', '$manufacturerFinal', '$itemWeightFinal', '$sizeFinal', '$specialFeaturesFinal', '$colorFinal', '$brandFinal', '$source')";
echo "<br> Product info before Query: ";
var_dump($sql);
$result = $mysqli->query($sql);

$productIdAmazon = $mysqli->insert_id;

echo "<br> THE PRODUCT ID IS:";
var_dump($productIdAmazon);

//ADD PRODCUT DESCRIPTION
// foreach ($descriptions as $productDescription) {
//     $productDescription = trim($mysqli->real_escape_string($productDescription));
//     $descriptionInsertSql = "INSERT INTO `product_description` (`product_id`, `description_name`)
//                                 VALUES ('$productIdAmazon', '$productDescription')";
//     echo "<br> Description before Query: ";
//     var_dump($descriptionInsertSql);
//     $descriptionResult = $mysqli->query($descriptionInsertSql);
// }
// echo "<br><br>Description: ";
// var_dump($descriptions);
// //Additional Images

// //Horizontal Additional Images
// $horAdditionaImages = scrapeAmazon($scrapingURL, $apiKey);

// //Vertical Additional Images
// $vertAdditionaImages = [];
// $nodes = $xpath->query("//span[@class='a-button-text']//img/@src");
// foreach ($nodes as $node) {
//     $vertAdditionaImages[] = $node->value;
// }

// if (!empty($horAdditionaImages)) {
//     foreach ($horAdditionaImages as $index => $url) {

//         $insertAltImagesWalmart = "INSERT INTO `product_images` 
//         (`product_id`, `product_image_url`) 
//         VALUES('$productIdAmazon', '$url')";
//         echo "<br> Alt Hori Images before Query: ";
//         var_dump($insertAltImagesWalmart);
//         $altImgResultAmazon = $mysqli->query($insertAltImagesWalmart);
//     }
// } else {
//     echo json_encode("No Horizantal Images");
// }
// echo "<br><br>Horizontal Images: ";
// var_dump($horAdditionaImages);

// if (!empty($vertAdditionaImages)) {
//     foreach ($vertAdditionaImages as $index => $url) {

//         $insertAltImagesWalmart = "INSERT INTO `product_images` 
//         (`product_id`, `product_image_url`) 
//         VALUES('$productIdAmazon', '$url')";
//         echo "<br> Alt Vertical Images before Query: ";
//         var_dump($insertAltImagesWalmart);
//         $altImgResultAmazon = $mysqli->query($insertAltImagesWalmart);
//     }
// } else {
//     echo json_encode("No Horizantal Images");
// }
// echo "<br><br>Vertical Images: ";
// var_dump($vertAdditionaImages);
//Error Handling
if (!$result) {
    //echo json_encode(["Product Error:" => $mysqli->error]);
    var_dump($mysqli->error);
    exit();
} else {
    echo "<br>Result Query: ";
    var_dump($result);
    //echo json_encode($descriptionResult);
    echo json_encode(["Result:" => "The Insert Query Done!"]);
}
