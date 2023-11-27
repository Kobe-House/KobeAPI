<?php

// Reporting All Errors 

use GuzzleHttp\Promise\Is;

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

//---- SELECT QUERY -----
$sql = "SELECT p.product_id, p.source, p.created_at, p.title, p.image_url, p.item_model, p.parcel_dimensions, p.asin, p.manufacturer, p.item_weight, p.size, p.special_features, p.color, p.brand, pd.product_description_id, pd.description_name, pi.product_images_id, pi.product_image_url
        FROM product p
        LEFT JOIN product_description pd ON p.product_id = pd.product_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        ORDER BY p.product_id, pd.product_description_id, pi.product_images_id";

$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode(["Error:" => $mysqli->error]);
} else {
    $tempProducts = [];

    while ($data = $result->fetch_assoc()) {
        $productId = $data['product_id'];
        if ($data['asin'] !== null) {
            $asin = str_replace("\xE2\x80\x8E", "", $data['asin']);
        } else {
            $asin = $data['manufacturer'];
        }
        if ($data['manufacturer'] !== null) {
            $productManufacturer = str_replace("\xE2\x80\x8E", "", $data['manufacturer']);
        } else {
            $productManufacturer = $data['manufacturer'];
        }

        if (!isset($tempProducts[$productId])) {
            $tempProducts[$productId] = [
                'productId' => $productId,
                'productTitle' => $data['title'],
                'source' => strtoupper($data['source']),
                'productAsin' => $asin,
                'productManufacturer' => $productManufacturer,
                'productBrand' => $data['brand'],
                'productWeight' => $data['item_weight'],
                'productDimension' => $data['parcel_dimensions'],
                'productModalNumber' => $data['item_model'],
                'productSpecailFeatures' => $data['special_features'],
                'productColor' => $data['color'],
                'productSize' => $data['size'],
                'imageURL' => stripslashes($data['image_url']),
                'productDescriptions' => [],
                'productImages' => [],
            ];
        }

        if ($data['product_description_id'] !== null && !in_array(['id' => $data['product_description_id'], 'name' => $data['description_name']], $tempProducts[$productId]['productDescriptions'], true)) {
            $tempProducts[$productId]['productDescriptions'][] = ['id' => $data['product_description_id'], 'name' => $data['description_name']];
        }

        if ($data['product_images_id'] !== null && !in_array(['id' => $data['product_images_id'], 'url' => $data['product_image_url']], $tempProducts[$productId]['productImages'], true)) {
            $tempProducts[$productId]['productImages'][] = ['id' => $data['product_images_id'], 'url' => $data['product_image_url']];
        }
    }

    $finalProducts = array_values($tempProducts);

    if (empty($finalProducts)) {
        echo json_encode(["Message" => "There's Nothing"]);
    } else {
        echo json_encode($finalProducts);
    }
}
