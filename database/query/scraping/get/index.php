<?php

// Reporting All Errors 
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Setting Headers for Cross Origin Resource Sharing
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Return only the headers and not the content
    //header('Access-Control-Allow-Origin: http://localhost:3000');
    header("Access-Control-Allow-Origin: https://sellerzone.io");
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Content-Type: application/json;');
    exit(0);
}
// Regular request processing
//header('Access-Control-Allow-Origin: http://localhost:3000');
header("Access-Control-Allow-Origin: https://sellerzone.io");
header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Content-Type: application/json;');

//---  Autoloads -----
require '../../../../vendor/autoload.php';

//Firebase to decode the token and get claims
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//Database Connection
include('../../../migrations/2023083000000_scraping_bulk_kobe.php');
$connection = new Connection();
$mysqli = $connection->getConnection();
//---- Processing Query Result Function --------
function processProducts($result)
{
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

$addon = "";
$sql = "SELECT p.product_id, p.user_guid, p.source, p.created_at, p.title, p.image_url, p.item_model, p.parcel_dimensions, p.asin, p.manufacturer, p.item_weight, p.size, p.special_features, p.color, p.brand, pd.product_description_id, pd.description_name, pi.product_images_id, pi.product_image_url
        FROM product p
        LEFT JOIN product_description pd ON p.product_id = pd.product_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id";

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    preg_match('/Bearer\s(\S+)/', $authHeader, $matches);
    if (isset($matches[1])) {
        $token = $matches[1];
        $urufunguzo = "4d46e827c8c29048f1b3de28123ed620fee5f1ab68d5fad46e57c7b3b3e66e45";
        try {
            $decode = JWT::decode($token, new Key($urufunguzo, 'HS512'));
            $access = $decode->level;
            $guid = $decode->guid;
            if ($access == "2") {
                $sql .= " ORDER BY 
                            p.created_at DESC,
                            p.product_id DESC,
                            pd.product_description_id DESC,
                            pi.product_images_id DESC";
                $result = $mysqli->query($sql);
                if (!$result) {
                    echo json_encode(["Error:" => $mysqli->error]);
                } else {
                    processProducts($result);
                }
            } elseif ($access == "0") {
                $addon = " WHERE p.user_guid = '$guid'";
                $sql .= $addon;

                $sql .= " ORDER BY 
                            p.created_at DESC,
                            p.product_id DESC,
                            pd.product_description_id DESC,
                            pi.product_images_id DESC";
                $result = $mysqli->query($sql);

                if (!$result) {
                    echo json_encode(["Error:" => $mysqli->error]);
                } else {
                    processProducts($result);
                }
            } else {
                echo json_encode(http_response_code(401));
            }
        } catch (Exception $e) {
            echo json_encode(["message" => "Error: " . $e->getMessage()]);
        }
    } else {
        echo "NOT FOUND";
    }
} else {
    http_response_code(401);
    echo json_encode("BYANZE");
}
