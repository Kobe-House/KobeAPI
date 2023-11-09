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

//Get the Scraped Data
//$sql = "SELECT * FROM `product`";
$sql = "SELECT p.product_id, p.source, pi.image_id, pi.product_image_url, pi.product_images_id, pi.product_id, p.created_at, p.title, p.image_url, p.item_model, p.parcel_dimensions, p.asin, p.manufacturer, p.item_weight, p.size, p.special_features, p.color, p.brand, pd.description_name, pd.product_description_id
        FROM product p
        LEFT JOIN product_description pd
        ON p.product_id = pd.product_id
        LEFT JOIN product_images pi
        ON pi.product_id = p.product_id
        ORDER BY p.created_at DESC ";

$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode(["Error:" => $mysqli->error]);
} else {
    // Initialize an array to hold fetched data
    $products = [];

    if ($result->num_rows > 0) {
        while ($data = $result->fetch_assoc()) {
            $productId = trim($data['product_id']);
            $productTitle = trim($data['title']);
            if ($data['asin'] !== null) {
                $productAsin = str_replace("\xE2\x80\x8E", "", $data['asin']);
            } else {
                $productAsin = $data['asin'];
            }
        
            if ($data['manufacturer'] !== null) {
                $productManufacturer = str_replace("\xE2\x80\x8E", "", $data['manufacturer']);
            } else {
                $productManufacturer = $data['manufacturer'];
            }
            $productBrand = $data['brand'];
            $productSource = $data['source'];
            $productWeight = $data['item_weight'];
            $productDimension = $data['parcel_dimensions'];
            $productModalNumber = $data['item_model'];
            $productSpecailFeatures = $data['special_features'];
            $productColor = $data['color'];
            $productSize = $data['size'];
            $imageURL = stripslashes($data['image_url']);

            //Product Description
            if (isset($data['product_description_id']) && !empty($data['product_description_id'])) {
                $productDescriptionId = trim($data['product_description_id']);
            } else {
                $productDescriptionId = -1;
            }
            $productDescription = [
                'productDescriptionId' => $productDescriptionId,
                'descriptionName' => $data['description_name']
            ];

            //Product Alternative Images
            // if (isset($data['product_image_url']) && !empty($data['product_image_url'])) {
            //     $productDescriptionId = trim($data['product_image_url']);
            // } else {
            //     $productDescriptionId = -1;
            // }
            // $productAlternativeImg = [

            // ];
    

            // Check if the product exists in the array and append the description
            $found = false;
            foreach ($products as &$product) {
                if ($product["productTitle"] === $productTitle) {
                    $product["productDescriptions"][] = $productDescription;
                    $found = true;
                    break;
                }
            }
            // If the product doesn't exist in the array, create a new entry
            if (!$found) {
                $newProduct = [
                    'productId' => $productId,
                    'productTitle' => $productTitle,
                    'source' => $productSource,
                    'productAsin' => $productAsin,
                    'productManufacturer' => $productManufacturer,
                    'productBrand' => $productBrand,
                    'productWeight' => $productWeight,
                    'productDimension' => $productDimension,
                    'productModalNumber' => $productModalNumber,
                    'productSpecailFeatures' => $productSpecailFeatures,
                    'productColor' => $productColor,
                    'productSize' => $productSize,
                    'imageURL' => $imageURL,
                    'productDescriptions' => [$productDescription]
                ];
                $products[] = $newProduct;
            }
        }
    } else {
        echo json_encode(["Message" => "There's Nothing"]);
    }

    echo json_encode($products);
}

?>