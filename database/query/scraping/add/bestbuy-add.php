<?php

// Extract the product title
$productTitleBestBuy = $xpath->query("//h1[@class='productName_2KoPa']")[0]->textContent;
if (empty($productTitleBestBuy)) {
    $productTitleBestBuy = 'N/A';
}
// Extract the brand
$brandLink = $xpath->query("//div[@class='modelInformation__LaWR']//a[@class='link_3hcyN brand-link']")[0];
if (empty($brandLink)) {
    $brandNameBestBuy = 'N/A';
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
if (!$result && !$descriptionResult) {
    //echo json_encode(["Product Error:" => $mysqli->error]);
    var_dump($mysqli->error);
    exit();
} else {
    echo "<br>Result Query: ";
    var_dump($result);
    //echo json_encode($descriptionResult);
    echo json_encode(["Result:" => "The Insert Query Done!"]);
}
